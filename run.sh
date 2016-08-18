#!/bin/bash


echo "##############################"
echo "#   Creating Documentation   #"
echo "##############################"
./vendor/phpdocumentor/phpdocumentor/bin/phpdoc -d ./src/ -t docs/api/

echo "##############################"
echo "# PHP Duplicate Code Checker #"
echo "##############################"
echo ""
vendor/sebastian/phpcpd/phpcpd $1

echo "##############################"
echo "#      PHP Mess Detector     #"
echo "##############################"
echo ""
php vendor/phpmd/phpmd/src/bin/phpmd $1 text codesize,unusedcode,naming

echo "##############################"
echo "#      PHP Code Sniffer      #"
echo "##############################"
echo ""
./vendor/bin/phpcs --standard=PSR2 --colors --tab-width=4 --encoding=utf-8 $1

if [ "$2" == "fix" ] ;
then
	./vendor/bin/phpcbf --standard=PSR2 --colors --tab-width=4 --encoding=utf-8 $1	
fi
