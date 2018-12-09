#!/bin/ksh

# run from project root

EXEC_DIR=$(pwd)
# give argument for name of zip to be created, ie user-leon-test-new-function

TMP_DIR=$HOME/plugintmp
mkdir -p $TMP_DIR

rm -Rf $TMP_DIR/wordpress-static-html-plugin
mkdir $TMP_DIR/wordpress-static-html-plugin

cp -r ./{languages,library,readme.txt,views,wp2static.php} $TMP_DIR/wordpress-static-html-plugin/

cd $TMP_DIR

# tidy permissions
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;

# strip comments and whitespace from each PHP file
find .  -name \*.php -exec $EXEC_DIR/provisioning/compress_php_file {} \;

zip -r -9 ./$1.zip ./wordpress-static-html-plugin

cd -

cp $TMP_DIR/$1.zip $HOME/Downloads/
