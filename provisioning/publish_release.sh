#!/bin/bash

# getting the repo locally:
# svn co https://plugins.svn.wordpress.org/your-plugin-name $HOME/svnplugindir


PROJECT_ROOT=$(pwd)
SVN_ROOT=$HOME/svnplugindir
NEW_TAG=2.5

# run from project root

# copy all files to svn trunk
cp -r $PROJECT_ROOT/* $SVN_ROOT/trunk/

# remove files only used in development
rm $SVN_ROOT/trunk/*.sh
rm $SVN_ROOT/trunk/*.rb
rm $SVN_ROOT/trunk/*.ini
rm $SVN_ROOT/trunk/*.zip
rm $SVN_ROOT/trunk/*.swo
rm $SVN_ROOT/trunk/Dockerfile
rm $SVN_ROOT/trunk/circle.yml
rm $SVN_ROOT/trunk/Gemfile*
rm $SVN_ROOT/trunk/readme.md
rm $SVN_ROOT/trunk/package-lock.json
rm $SVN_ROOT/trunk/package.json
rm -r $SVN_ROOT/trunk/wpassets
rm -r $SVN_ROOT/trunk/php53tests
rm -r $SVN_ROOT/trunk/node_modules
rm -r $SVN_ROOT/trunk/php5testvm
rm -r $SVN_ROOT/trunk/provisioning
rm -r $SVN_ROOT/trunk/demo_site_content

# image assets (for WP official pages) need to go into /assets, not /trunk
cp -r $PROJECT_ROOT/wpassets/* $SVN_ROOT/assets/

cd $SVN_ROOT

# TODO: linting, spaces to tabs, etc

###
#
#
#  Manually perform these steps from now on
#  due to having completely hosed SVN repo before! 
#
#
###

## tell svn to add the files 
## TODO: needs forcing to ensure all files added
#svn add --force * --auto-props --parents --depth infinity -q
##svn add trunk/*
#
## svn commit trunk
#svn ci -m "adding files for release $NEW_TAG"
#
## svn create tag
#svn cp trunk tags/$NEW_TAG
#
## push tag up
#svn ci -m "new tag $NEW_TAG"
