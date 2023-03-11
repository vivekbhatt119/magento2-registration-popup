# Mage2 Module Vivek RegistrationPopup

    ``vivek/module-registrationpopup``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)
 - [How To Use](#markdown-header-how-to-use)


## Main Functionalities
Register customer through popup.

## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/Vivek`
 - Enable the module by running `php bin/magento module:enable Vivek_RegistrationPopup`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require vivek/module-registrationpopup`
 - enable the module by running `php bin/magento module:enable Vivek_RegistrationPopup`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`



## How To Use
Just add below code to your PHTML file:

```
<a href="#" class="registration-link"><?= __("Create An Account") ?></a>
<script>
    require([
            'jquery',
            'Vivek_RegistrationPopup/js/model/registration-popup'
        ],
        function ($, siginup) {
            $(document).ready(function () {
                $(document).on("click", ".registration-link", function (e) {
                    e.preventDefault();
                    siginup.showModal();
                })
            })
        });
</script>
```


