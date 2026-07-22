#!/usr/bin/env bash
set -Eeuo pipefail

app_slug="${1:?Application slug is required}"
install_dev_tools="${2:-0}"

wordpress_path="/usr/src/wordpress"
dependency_root="/opt/cetech/dependencies"

install_manifest() {
  local manifest="$1"

  if [[ ! -f "${manifest}" ]]; then
    return 0
  fi

  while IFS='|' read -r slug version; do
    slug="${slug%%#*}"
    slug="$(printf '%s' "${slug}" | tr -d '\r\n' | xargs)"
    version="$(printf '%s' "${version:-}" | tr -d '\r\n' | xargs)"

    if [[ -z "${slug}" ]]; then
      continue
    fi

    if [[ -z "${version}" ]]; then
      echo "Missing version for ${slug} in ${manifest}" >&2
      exit 1
    fi

    echo "Installing ${slug} ${version}"

    tmp_zip="/tmp/${slug}-${version}.zip"
    curl -fL "https://downloads.wordpress.org/plugin/${slug}.${version}.zip" -o "${tmp_zip}"

    unzip -q -o "${tmp_zip}" -d "${wordpress_path}/wp-content/plugins"
    rm -f "${tmp_zip}"
  done < "${manifest}"
}

mkdir -p \
  "${wordpress_path}/wp-content/plugins" \
  /opt/cetech/wp-content/plugins

install_manifest \
  "${dependency_root}/common-public.lock"

install_manifest \
  "${dependency_root}/${app_slug}-public.lock"

if [[ "${install_dev_tools}" == "1" ]]; then
  install_manifest \
    "${dependency_root}/local-development.lock"
fi

rsync -a \
  "${wordpress_path}/wp-content/plugins/" \
  /opt/cetech/wp-content/plugins/

find /opt/cetech/wp-content/plugins \
  -type d \
  -exec chmod 0755 {} +

find /opt/cetech/wp-content/plugins \
  -type f \
  -exec chmod 0644 {} +

chown -R www-data:www-data \
  /opt/cetech/wp-content/plugins
