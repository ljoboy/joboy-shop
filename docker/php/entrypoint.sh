#!/bin/sh
# Entrypoint de l'application Laravel sur FrankenPHP.
#
#   app-entrypoint env:build  -> injecte les variables d'environnement dans .env
#                               (utilisé pendant le build de l'image)
#   app-entrypoint [cmd...]  -> injecte .env, attend MariaDB, migre, puis
#                               exécute la commande (par défaut FrankenPHP)

set -e

APP_DIR="${APP_DIR:-/app}"
ENV_FILE="${ENV_FILE:-${APP_DIR}/.env}"
ENV_EXAMPLE="${ENV_EXAMPLE:-${APP_DIR}/.env.example}"

cd "${APP_DIR}"

# Remplace (ou ajoute) une clé dans le fichier .env sans eval par le shell.
set_env() {
    key="$1"
    value="$2"

    [ -n "${value}" ] || return 0

    if grep -q "^${key}=" "${ENV_FILE}" 2>/dev/null; then
        # BSD/GNU sed compatible
        sed -i.bak "s|^${key}=.*|${key}=${value}|" "${ENV_FILE}" && rm -f "${ENV_FILE}.bak"
    else
        printf '%s=%s\n' "${key}" "${value}" >> "${ENV_FILE}"
    fi
}

# Crée le .env à partir du .env.example s'il n'existe pas encore.
ensure_env_file() {
    if [ ! -f "${ENV_FILE}" ]; then
        echo ">> Création de ${ENV_FILE} depuis $(basename "${ENV_EXAMPLE}")"
        cp "${ENV_EXAMPLE}" "${ENV_FILE}"
    fi
}

# Injecte les paramètres de base (base de données, app, sessions, cache).
inject_env() {
    set_env APP_NAME "${APP_NAME}"
    set_env APP_ENV "${APP_ENV}"
    set_env APP_DEBUG "${APP_DEBUG}"
    set_env APP_URL "${APP_URL}"

    if [ -n "${DB_CONNECTION}" ]; then
        set_env DB_CONNECTION "${DB_CONNECTION}"
        set_env DB_HOST "${DB_HOST}"
        set_env DB_PORT "${DB_PORT}"
        set_env DB_DATABASE "${DB_DATABASE}"
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

# Attend que la base de données réponde (max ~60s).
wait_for_database() {
    if [ -z "${DB_HOST}" ] || [ -z "${DB_DATABASE}" ]; then
        return 0
    fi

    tries=0
    echo ">> Attente de MariaDB sur ${DB_HOST}:${DB_PORT:-3306} ..."
    while [ "${tries}" -lt 60 ]; do
        if php -r '
            $host = getenv("DB_HOST");
            $port = (int) (getenv("DB_PORT") ?: 3306);
            $tries = 0;
            while ($tries++ < 3) {
                $fp = @fsockopen($host, $port, $errno, $errstr, 2);
                if ($fp) { fclose($fp); exit(0); }
                usleep(300000);
            }
            exit(1);
        ' 2>/dev/null; then
            echo ">> MariaDB est disponible."
            return 0
        fi
        tries=$((tries + 1))
        sleep 1
    done

    echo "!! MariaDB injoignable après ${tries}s, on continue quand même."
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

if [ -z "${APP_KEY}" ] || ! grep -q '^APP_KEY=base64:' "${ENV_FILE}" 2>/dev/null; then
    echo ">> Génération de APP_KEY"
    php artisan key:generate --force --ansi
fi

mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

[ -e "${APP_DIR}/public/storage" ] || php artisan storage:link --ansi

wait_for_database

php artisan config:clear --ansi >/dev/null 2>&1 || true

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo ">> Application des migrations ..."
    php artisan migrate --force --ansi
fi

if [ "${RUN_SEEDERS:-false}" = "true" ]; then
    echo ">> Exécution des seeders ..."
    php artisan db:seed --force --ansi
fi

if [ "${APP_ENV}" = "production" ] || [ "${RUN_OPTIMIZE:-true}" = "true" ]; then
    echo ">> Optimisation des caches Laravel ..."
    php artisan config:cache --ansi
    php artisan route:cache --ansi
    php artisan view:cache --ansi
    php artisan event:cache --ansi
fi

exec "$@"
