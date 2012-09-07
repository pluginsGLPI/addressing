#!/bin/bash

#soft='GLPI - Addressing plugin'
#version='2.1.0'
#email='glpi-translation@gna.org'
#copyright='Addressing Development Team'

#xgettext *.php */*.php -copyright-holder='$copyright' --package-name=$soft --package-version=$version --msgid-bugs-address=$email -o locales/en_GB.po -L PHP --from-code=UTF-8 --force-po  -i --keyword=_n:1,2 --keyword=__ --keyword=_e

xgettext *.php */*.php --copyright-holder='Addressing Development Team' --package-name='GLPI - Addressing plugin' --package-version='2.1.0' --msgid-bugs-address='glpi-translation@gna.org' -o locales/glpi.pot -L PHP --add-comments=TRANS \
--exclude-file=../../locales/glpi.pot --from-code=UTF-8 --force-po  \
	--keyword=_n:1,2 --keyword=__s --keyword=__ --keyword=_e --keyword=_x:1c,2 --keyword=_ex:1c,2 --keyword=_nx:1c,2,3


