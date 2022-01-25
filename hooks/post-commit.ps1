#!/usr/bin/env pwsh
# Create the release package.
mkdir settings-page-wp
cp -r config src composer.json LICENSE README.md settings-page-wp
tar -a -cf settings-page-wp.zip settings-page-wp
rm -r settings-page-wp
