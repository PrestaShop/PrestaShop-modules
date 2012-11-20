# Jirafe Module for Prestashop

This project builds a Prestashop module which automatically integrates Jirafe analytics into the Prestashop ecommerce platform.

Note that this project uses the Jirafe PHP Client as a submodule, so you cannot just download it.  Please follow the installation instructions below.

## Installation

First of all, clone project:

    git clone git@github.com:jirafe/prestashop-module.git

Then, init/update all project submodules:

    cd (path_to_jirafe_module)
    git submodule update --init --recursive

Link this directory to your prestashop ecommerce platform root directory

    cd (path_to_prestashop_platform)/modules
    ln -s (path_to_jirafe_module) jirafe

To enable the Jirafe module for Prestashop, log into prestashop, click on the 'Modules' tab, and open the 'Stats and Analytics' item.  Click 'Install' next to the Jirafe Analytics module.

## For developers only

### Deployment

To create a clean zip file of the Jirafe module for Prestashop:

    cd (path_to_jirafe_module)
    zip -r ../jirafe.zip . -x *.git*
    
### Uninstallation

To manually remove the plugin data from the prestashop database:

    DELETE FROM ps_configuration WHERE name LIKE 'JIRAFE%';
    DELETE FROM ps_module WHERE name = 'jirafe';

To remove the module from the prestashop platform:

    rm -rf (path_to_prestashop_platform)/modules/jirafe
    
