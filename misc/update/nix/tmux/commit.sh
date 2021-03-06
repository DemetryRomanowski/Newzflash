#!/usr/bin/env bash

if [ -e "newzflash_base.php" ]
then
	export NZEDB_ROOT="$(pwd)"
else
	export NZEDB_ROOT="$(php ../../../../newzflash_base.php)"
fi
echo -e "Site root is ${NZEDB_ROOT}\n"

nano ${NZEDB_ROOT}/Changelog
cd ${NZEDB_ROOT}
#commit=`git log | grep "^commit" | wc -l`
#commit=`expr $commit + 1`

#sed -i -e "s/\$version=.*$/\$version=\"0.3r$commit\";/"  ${NZEDB_ROOT}/misc/update/nix/tmux/monitor.php
php "${NZEDB_ROOT}/newzflash/utility/Versions.php 1"

git commit -a
