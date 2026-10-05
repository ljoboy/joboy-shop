#!/bin/sh
# Entrypoint de l'application Laravel sur FrankenPHP.
#
#   app-entrypoint env:build  -> injecte les variables d'environnement dans .env
#                               (utilisé pendant le build de l'image)
#   app-entrypoint [cmd...]  -> injecte .env, prépare SQLite, migre, puis
#                               exécute la commande (par défaut FrankenPHP)

set -e

APP_DIR="${APP_DIR:-/app}"
ENV_FILE="${ENV_FILE:-${APP_DIR}/.env}"
ENV_EXAMPLE="${ENV_EXAMPLE:-${APP_DIR}/.env.example}"
ENV_TMP_DIR="${ENV_TMP_DIR:-/tmp}"

cd "${APP_DIR}"

# Vrai si le .env peut être écrit (système de fichiers en lecture seule ->
# les variables d'environnement réelles du conteneur sont alors utilisées,
# elles ont de toute façon priorité sur le .env avec le Dotenv immuable).
ENV_WRITABLE=true

can_write_env() {
    if [ "${ENV_WRITABLE}" != "true" ]; then
        return 1
    fi

    if [ -w "${ENV_FILE}" ] 2>/dev/null; then
        return 0
    fi

    ENV_WRITABLE=false
    echo ">> ${ENV_FILE} non inscriptible : variables d'environnement utilisées telles quelles."
    return 1
}

# Échappe les caractères spéciaux de la substitution sed (\, & et le délimiteur |).
sed_escape() {
    printf '%s' "$1" | sed -e 's/[\\&|]/\\&/g'
}

# Encode une valeur pour un fichier .env lu par phpdotenv.
# Une valeur nue est acceptée uniquement si elle ne contient aucun caractère
# spécial ; sinon elle est entourée de guillemets doubles, car
# "APP_NAME=Joboy Shop" est invalide ("unexpected whitespace").
# "=" est toléré pour les valeurs base64 (APP_KEY), le premier "=" servant
# déjà de séparateur.
env_quote() {
    value="$1"

    if printf '%s' "${value}" | grep -q '^[A-Za-z0-9_./:@%+=!-]\{1,\}$'; then
        printf '%s' "${value}"
        return 0
    fi
    printf '"%s"' "$(printf '%s' "${value}" | sed -e 's/\\/\\\\/g' -e 's/"/\\"/g')"
}

# Remplace (ou ajoute) une clé dans le fichier .env sans eval par le shell.
#
# Le temporaire est créé dans ENV_TMP_DIR et non à côté de ENV_FILE : le
# répertoire /app appartient à root alors que le conteneur tourne en www-data,
# et "sed -i" échoue ("Permission denied" sur /app/sedXXXX) dès lors que le
# fichier .env est inscriptible mais pas son dossier parent.
set_env() {
    key="$1"
    value="$2"

    [ -n "${value}" ] || return 0
    can_write_env || return 0

    escaped=$(sed_escape "$(env_quote "${value}")")
    tmp="${ENV_TMP_DIR}/app-env.$$"

    if grep -q "^${key}=" "${ENV_FILE}" 2>/dev/null; then
        sed "s|^${key}=.*|${key}=${escaped}|" "${ENV_FILE}" > "${tmp}" || return 0
    else
        cp "${ENV_FILE}" "${tmp}" || return 0
        printf '%s=%s\n' "${key}" "${escaped}" >> "${tmp}"
    fi

    # "cat >" tronque puis réécrit le fichier cible : seules les permissions du
    # fichier lui-même sont requises, pas celles de son répertoire.
    if ! cat "${tmp}" > "${ENV_FILE}"; then
        echo "!! Écriture de ${ENV_FILE} impossible (permissions ?)."
        rm -f "${tmp}"
        return 0
    fi

    rm -f "${tmp}"
}

# Crée le .env à partir du .env.example s'il n'existe pas encore.
ensure_env_file() {
    if [ ! -f "${ENV_FILE}" ]; then
        if [ -r "${ENV_EXAMPLE}" ] && touch "${ENV_FILE}" 2>/dev/null; then
            echo ">> Création de ${ENV_FILE} depuis $(basename "${ENV_EXAMPLE}")"
            cp "${ENV_EXAMPLE}" "${ENV_FILE}"
        else
            ENV_WRITABLE=false
            echo ">> Impossible de créer ${ENV_FILE} : variables d'environnement utilisées telles quelles."
        fi
    fi
}

# Injecte les paramètres de base (base de données, app, sessions, cache).
inject_env() {
    set_env APP_NAME "${APP_NAME}"
    set_env APP_ENV "${APP_ENV}"
    set_env APP_DEBUG "${APP_DEBUG}"
    set_env APP_URL "${APP_URL}"

    set_env DB_CONNECTION "${DB_CONNECTION}"
    set_env DB_DATABASE "${DB_DATABASE}"

    if [ "${DB_CONNECTION}" != "sqlite" ]; then
        set_env DB_HOST "${DB_HOST}"
        set_env DB_PORT "${DB_PORT}"
        set_env DB_USERNAME "${DB_USERNAME}"
        set_env DB_PASSWORD "${DB_PASSWORD}"
        set_env DB_CHARSET "${DB_CHARSET:-utf8mb4}"
        set_env DB_COLLATION "${DB_COLLATION:-utf8mb4_unicode_ci}"
    fi

    set_env SESSION_DRIVER "${SESSION_DRIVER:-database}"
    set_env CACHE_STORE "${CACHE_STORE:-database}"
    set_env QUEUE_CONNECTION "${QUEUE_CONNECTION:-database}"
    set_env BROADCAST_CONNECTION "${BROADCAST_CONNECTION:-log}"
    set_env LOG_CHANNEL "${LOG_CHANNEL:-stack}"
    set_env LOG_STACK "${LOG_STACK:-stderr}"
    set_env MAIL_MAILER "${MAIL_MAILER:-log}"
    set_env FILESYSTEM_DISK "${FILESYSTEM_DISK:-local}"
}

# Crée le fichier SQLite et son répertoire parent si nécessaire.
# Retourne 1 si le chemin n'est pas inscriptible.
ensure_sqlite_database() {
    db_path="${DB_DATABASE:-${APP_DIR}/database/database.sqlite}"

    db_dir=$(dirname "${db_path}")

    if [ ! -d "${db_dir}" ]; then
        mkdir -p "${db_dir}" 2>/dev/null || true
    fi

    if [ ! -f "${db_path}" ]; then
        touch "${db_path}" 2>/dev/null || true
    fi

    if [ ! -f "${db_path}" ] || [ ! -w "${db_path}" ]; then
        echo "!! Fichier SQLite '${db_path}' absent ou non inscriptible."
        echo "!! Vérifiez que le volume monté sur ${db_dir} est accessible."
        return 1
    fi

    echo ">> Base SQLite prête : ${db_path}"
    return 0
}

# Attend que la base de données réponde (max ~60s).
# Retourne 1 si l'hôte est introuvable au niveau DNS.
wait_for_database() {
    if [ -z "${DB_HOST}" ] || [ -z "${DB_DATABASE}" ]; then
        echo "!! DB_HOST ou DB_DATABASE non défini : connexion à la base impossible."
        return 0
    fi

    echo ">> Base : ${DB_USERNAME}@${DB_HOST}:${DB_PORT:-3306}/${DB_DATABASE}"

    # 0 = connexion OK, 2 = nom d'hôte non résolu, 1 = pas encore joignable
    probe='
$host  = (string) getenv("DB_HOST");
$port  = (int) (getenv("DB_PORT") ?: 3306);
$limit = time() + 6;
do {
    $fp = @fsockopen($host, $port, $errno, $errstr, 2);
    if ($fp) { fclose($fp); exit(0); }
    $resolved = gethostbyname($host);
    if ($resolved === $host && !filter_var($host, FILTER_VALIDATE_IP)) { exit(2); }
    usleep(300000);
} while (time() < $limit);
exit(1);
'

    tries=0
    while [ "${tries}" -lt 60 ]; do
        rc=0
        php -r "${probe}" 2>/dev/null || rc=$?

        case "${rc}" in
            0)
                echo ">> Base de données disponible."
                return 0
                ;;
            2)
                echo "!! Hôte '${DB_HOST}' introuvable (DNS)."
                return 1
                ;;
        esac

        tries=$((tries + 1))
        sleep 1
    done

    echo "!! ${DB_HOST}:${DB_PORT:-3306} injoignable après ${tries}s, on continue quand même."
    return 0
}

# --- Mode build : uniquement l'injection du .env -----------------------------
if [ "$1" = "env:build" ]; then
    ensure_env_file
    inject_env
    exit 0
fi

# --- Mode runtime ------------------------------------------------------------
ensure_env_file
inject_env

if [ "${ENV_WRITABLE}" = "true" ]; then
    if [ -z "${APP_KEY}" ] || ! grep -q '^APP_KEY=base64:' "${ENV_FILE}" 2>/dev/null; then
        if [ -n "${APP_KEY}" ]; then
            echo ">> Injection de APP_KEY depuis l'environnement"
            set_env APP_KEY "${APP_KEY}"
        else
            echo ">> Génération de APP_KEY"
            php artisan key:generate --force --ansi
        fi
    fi
elif [ -z "${APP_KEY}" ]; then
    echo "!! Aucune APP_KEY : sessions et cookies chiffrés ne fonctionneront pas."
    echo "!! Définissez APP_KEY (base64:...) dans l'environnement du conteneur."
fi

mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    || echo "!! Répertoires storage/bootstrap/cache non inscriptibles."

if [ ! -e "${APP_DIR}/public/storage" ]; then
    php artisan storage:link --ansi || true
fi

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    if ! ensure_sqlite_database; then
        exit 1
    fi
else
    if ! wait_for_database; then
        echo "!! Arrêt : l'hôte de base '${DB_HOST}' ne peut pas être résolu."
        echo "!! Définissez DB_HOST (et DB_PORT/DB_DATABASE/DB_USERNAME/DB_PASSWORD)"
        echo "!! dans les variables d'environnement du conteneur sur Dokploy."
        exit 1
    fi
fi

php artisan config:clear --ansi >/dev/null 2>&1 || true

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo ">> Application des migrations ..."
    if ! php artisan migrate --force --ansi; then
        echo "!! Échec des migrations. Vérifiez DB_CONNECTION, DB_HOST, DB_PORT,"
        echo "!! DB_DATABASE, DB_USERNAME et DB_PASSWORD du conteneur."
        exit 1
    fi
fi

if [ "${RUN_SEEDERS:-false}" = "true" ]; then
    echo ">> Exécution des seeders ..."
    php artisan db:seed --force --ansi
fi

if [ "${APP_ENV}" = "production" ] || [ "${RUN_OPTIMIZE:-true}" = "true" ]; then
    if [ -w "${APP_DIR}/bootstrap/cache" ] 2>/dev/null; then
        echo ">> Optimisation des caches Laravel ..."
        php artisan config:cache --ansi
        php artisan route:cache --ansi
        php artisan view:cache --ansi
        php artisan event:cache --ansi
    else
        echo ">> bootstrap/cache non inscriptible : caches Laravel non précompilés."
    fi
fi

exec "$@"
