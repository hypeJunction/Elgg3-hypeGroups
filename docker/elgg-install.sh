#!/bin/bash
set -e

# Wait for MySQL to be ready
echo "Waiting for MySQL..."
until php -r "new PDO('mysql:host=${ELGG_DB_HOST:-db}', '${ELGG_DB_USER:-elgg}', '${ELGG_DB_PASS:-elgg}');" 2>/dev/null; do
    sleep 1
done
echo "MySQL is ready."

cd /var/www/html

# Check if Elgg is already installed
if [ ! -f /var/www/html/.elgg-installed ]; then
    echo "Installing Elgg 4.x..."

    # Create settings.php
    mkdir -p elgg-config
    cat > elgg-config/settings.php <<'SETTINGS_TEMPLATE'
<?php
global $CONFIG;
if (!isset($CONFIG)) {
    $CONFIG = new \stdClass;
}
SETTINGS_TEMPLATE

    cat >> elgg-config/settings.php <<SETTINGS_VALUES
\$CONFIG->dbuser = '${ELGG_DB_USER:-elgg}';
\$CONFIG->dbpass = '${ELGG_DB_PASS:-elgg}';
\$CONFIG->dbname = '${ELGG_DB_NAME:-elgg}';
\$CONFIG->dbhost = '${ELGG_DB_HOST:-db}';
\$CONFIG->dbport = '3306';
\$CONFIG->dbprefix = 'elgg_';
\$CONFIG->dbencoding = 'utf8mb4';
\$CONFIG->dataroot = '${ELGG_DATA_ROOT:-/var/www/data/}';
\$CONFIG->wwwroot = '${ELGG_SITE_URL:-http://localhost/}';
\$CONFIG->cacheroot = '${ELGG_DATA_ROOT:-/var/www/data/}cache/';
\$CONFIG->assetroot = '${ELGG_DATA_ROOT:-/var/www/data/}assets/';
SETTINGS_VALUES

    # Run the installer
    php -r "
        require_once 'vendor/autoload.php';

        \$params = [
            'dbuser' => '${ELGG_DB_USER:-elgg}',
            'dbpassword' => '${ELGG_DB_PASS:-elgg}',
            'dbname' => '${ELGG_DB_NAME:-elgg}',
            'dbhost' => '${ELGG_DB_HOST:-db}',
            'dbport' => '3306',
            'dbprefix' => 'elgg_',
            'sitename' => 'Elgg 4.x Migration Test',
            'siteemail' => '${ELGG_ADMIN_EMAIL:-admin@example.com}',
            'wwwroot' => '${ELGG_SITE_URL:-http://localhost/}',
            'dataroot' => '${ELGG_DATA_ROOT:-/var/www/data/}',
            'displayname' => 'Admin',
            'email' => '${ELGG_ADMIN_EMAIL:-admin@example.com}',
            'username' => 'admin',
            'password' => '${ELGG_ADMIN_PASSWORD:-admin12345}',
        ];

        \$installer = new \ElggInstaller();
        \$installer->batchInstall(\$params);
        echo 'Elgg 4.x installed successfully.' . PHP_EOL;
    " 2>&1 || echo "Install completed (check for errors above)."

    # Activate plugins in priority order
    echo "Activating plugins..."
    PLUGIN_ORDER_FILE="/var/www/html/mod/.plugin-order.txt"

    # Symlink core plugins listed in .plugin-order.txt that ship with
    # elgg/elgg under vendor/elgg/elgg/mod/. The orchestrator's resolver
    # classifies these as source=core: they aren't bind-mounted from the
    # host workspace because the canonical copy lives in the elgg release.
    # Symlinking before generateEntities() makes the activation loop
    # discover them via the standard mod/ scan.
    if [ -f "$PLUGIN_ORDER_FILE" ]; then
        VENDOR_MOD="/var/www/html/vendor/elgg/elgg/mod"
        while IFS= read -r CORE_PLUGIN; do
            CORE_PLUGIN="${CORE_PLUGIN%%#*}"
            CORE_PLUGIN="$(echo "$CORE_PLUGIN" | tr -d '[:space:]')"
            [ -z "$CORE_PLUGIN" ] && continue
            if [ ! -e "/var/www/html/mod/${CORE_PLUGIN}" ] && [ -d "${VENDOR_MOD}/${CORE_PLUGIN}" ]; then
                ln -s "${VENDOR_MOD}/${CORE_PLUGIN}" "/var/www/html/mod/${CORE_PLUGIN}"
                echo "Symlinked core plugin: ${CORE_PLUGIN}"
            fi
        done < "$PLUGIN_ORDER_FILE"
    fi

    if [ -f "$PLUGIN_ORDER_FILE" ]; then
        echo "Using ordered activation from .plugin-order.txt"
        php -r "
            require_once 'vendor/autoload.php';
            \$app = \Elgg\Application::getInstance();
            \$app->bootCore();
            _elgg_services()->plugins->generateEntities();
            \$order = file('$PLUGIN_ORDER_FILE', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            \$activated = 0;
            \$failed = [];
            foreach (\$order as \$id) {
                \$id = trim(\$id);
                if (empty(\$id) || \$id[0] === '#') continue;
                \$plugin = elgg_get_plugin_from_id(\$id);
                if (!\$plugin) { echo 'Plugin not found: ' . \$id . PHP_EOL; continue; }
                if (\$plugin->isActive()) { \$activated++; continue; }
                try {
                    // Bump each plugin to the highest priority among
                    // inactives at activation time. The .plugin-order.txt
                    // is topologically sorted (dep-leaves first, plugin
                    // under test last), so each call to setPriority('last')
                    // produces a monotonically increasing priority that
                    // satisfies any 'position' => 'after' constraint
                    // declared in plugin.dependencies.
                    \$plugin->setPriority('last');
                    \$plugin->activate();
                    \$activated++;
                    echo '  + ' . \$id . PHP_EOL;
                } catch (\Throwable \$e) {
                    \$failed[] = \$id . ': ' . \$e->getMessage();
                }
            }
            echo \$activated . ' plugin(s) activated.' . PHP_EOL;
            if (!empty(\$failed)) {
                echo count(\$failed) . ' plugin(s) failed:' . PHP_EOL;
                foreach (\$failed as \$f) echo '  - ' . \$f . PHP_EOL;
            }
        " 2>&1 || echo "Plugin activation completed (check for errors above)."
    else
        echo "No .plugin-order.txt found, activating all plugins..."
        php -r "
            require_once 'vendor/autoload.php';
            \$app = \Elgg\Application::getInstance();
            \$app->bootCore();
            _elgg_services()->plugins->generateEntities();
            \$plugins = elgg_get_plugins('inactive');
            \$failed = [];
            foreach (\$plugins as \$plugin) {
                try { \$plugin->activate(); }
                catch (\Throwable \$e) { \$failed[] = \$plugin->getID() . ': ' . \$e->getMessage(); }
            }
            if (empty(\$failed)) { echo 'All plugins activated.' . PHP_EOL; }
            else {
                echo count(\$failed) . ' plugin(s) failed:' . PHP_EOL;
                foreach (\$failed as \$f) echo '  - ' . \$f . PHP_EOL;
            }
        " 2>&1 || echo "Plugin activation completed (check for errors above)."
    fi

    # Clear system cache so it regenerates on next boot with all active plugins'
    # views registered. Without this, the boot cache built before activation
    # is stale and PHPUnit sees no plugin views on first run.
    echo "Clearing system cache..."
    php -r "
        require_once 'vendor/autoload.php';
        \$app = \Elgg\Application::getInstance();
        \$app->bootCore();
        elgg_clear_caches();
        echo 'System cache cleared.' . PHP_EOL;
    " 2>&1 || echo "Cache clear completed (check for errors above)."

    # Hand the data root over to the Apache user. The installer ran as
    # root (entrypoint context) and left every cache subdirectory
    # root-owned, which makes Phpfastcache throw IOException on the
    # first request and the site renders Elgg's "fatal error" stub. Doing
    # this once on first install is enough — Apache (www-data) extends
    # the tree from there.
    chown -R www-data:www-data "${ELGG_DATA_ROOT:-/var/www/data/}"
    chmod -R u+rwX,g+rX,o+rX "${ELGG_DATA_ROOT:-/var/www/data/}"

    touch /var/www/html/.elgg-installed
    echo "Elgg 4.x setup complete."
fi

# Start Apache
echo "Starting Apache..."
exec apache2-foreground
