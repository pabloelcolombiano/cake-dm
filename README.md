# cake-dm
A domain manager for CakePHP applications.

## Introduction

As your CakePHP grows, the respective size of your Controller, Model and Template folders increases.

Your *src/Template/Element* folder has probably become a mess too, if you haven't split it in several sub-folders.

New joiners wonder where to start and, just as you, do not grasp which models relate to another. 

An alternative would be to split everything into plugins. But this might not be what you expect from plugins. You would end up with a quantity of plugins added to the *real* plugins, and a deserted *src* folder. 

Sub-folders! this is the approach proposed by cake-dm. With domain separation in mind, the package proposes to split your MVC structure into domains. And sub-domains.

## Requirements

*CakePHP ^3.6*

## Installation

Use composer to install the domain manager

```bash
composer require webrider/cake-dm
```

## Example

### Without cake-dm
Assuming the following minimalistic structure for a flight company.
```bash
- src
    - Console
    - Controller
        - Components
            - GeneralComponent.php
            - UsersRelatedComponent.php       
        - AdminController.php
        - AppController.php
        - DiscountsController.php
        - FlightsController.php
        - InvoicesController.php
        - MachinesController.php
        - PagesController.php
        - PilotsController.php
        - SeatsController.php       
        - StewardsController.php
        - TicketsController.php
        - UsersController.php
        - UserProfilesController.php
    - Model
        - AppTable.php
        - DiscountsTable.php
        - FlightsTable.php
        ...
    - Template
        - Admin
        - Discounts
        - Flights
        - Element
            - company_logo.ctp
            - flight_description.ctp
            - user_avatar.ctp            
        - Invoices
        - Layout
            - default.ctp
        - Machines
        ...
    - View
        - Cell
        - Helper
        - AjaxView.php
        - AppView.php
```
### With cake-dm
Cake-dm makes it possible to organize your code as follows, with domain folders marked by arrows:
```
- src
    - Console
    - Domain
        -> Admin
            - Controller
                - AdminController.php
            - Template
                - Layout
                    - default.ctp 
        -> App
            - Controller
                - Components
                    - GeneralComponent.php                   
                - AppController.php
                - PagesController.php
            - Model
            - Template
                - Element
                    - company_logo.ctp
                - Layout
                    - default.ctp            
                
            - View
                - Cell
                - Helper
                - AjaxView.php
                - AppView.php               
            - Application.php        
        -> Invoicing
            - Controller              
                - DiscountsController.php
                - TicketsController.php
            - Model
                ...
            - Template
                ...
        -> Logistic
            - Controller
                - FlightsController.php
                - MachinesController.php
                - SeatsController.php
            - Model
                - FlightsTable.php
                ...
            - Template
                - Element
                    - Flight
                        - flight_description.ctp
                - Flights
                - Machines
                - Seats
        -> Staff
              ...
        -> User
            - Controller
                - Components                
                    - UsersRelatedComponent.php
                - UsersController.php
                - UserProfilesController.php
            - Model
                - UsersTable.php
                - UsersProfilesTable.php
            - Template
                - Element
                    - user_avatar.ctp
                - Users
                - UserProfile  
```
The advantage is that:
 - The structure of your app becomes clearer. 
 - The different domains of your code can be clearly assigned to different developers or teams.
 - Template paths pile up. For example here, the layout of all the templates within the domain Admin is different than the others. This can easily be read from the structure.
 - If a template, layout or element is not found in a domain, it will be searched in *Domain/App/Template*
 - The domain layer name *App* should actually be your app's namespace, which is *App* by default in CakePHP. For plugins, it should be *Plugin*, regardless of the plugin's name.
 - Elements and Components are spread through the domains according to their relevance.

Note that:
 - It is possible to split your domains in sub-domains, for example by creating a folder *src/Domain/Staff/Internal* and a folder *src/Domain/Staff/External*. In that case, no MVC folders should be located in the folder *src/Domain/Staff*
 - Plugin structures can be split in the same manner. You should then replace the *App* domain layer by a *Plugin* domain layer.
 - Namespaces do not change. It is therefore forbidden to have two controllers with the same name in different domains.
 - Cells, Views and Helpers can be distributed along domain layers too, just as the components. Behaviors too. 
 
## Settings

The structure is managed by three components.

### Composer

In order to have Cake following the paths without changing the namespaces and intruding in the middleware, you must modify the following in your composer.json file:
```bash
"autoload": {
    "psr-4": {
        "App\\": [
            "src",
            "src/Domain/Admin",
            "src/Domain/App",
            "src/Domain/Invoicing",
            "src/Domain/Logistic",
            "src/Domain/Staff",
            "src/Domain/User"
        ],
        ...
    }
},
```

Run
``` composer dump-autoload ```
after any structural change.

You may also have to refresh your cached routes as well in *tmp/cache*.

The same applies when introducing domain layers in your plugins.

### Controller

In your *AppController.php*, as early as in the *AppController::initialize()*, the relevant paths to the templates should be added.

Cake-dm will know in which layer the request was performed. It will include the template paths of that layer, as well as the *App* domain layer. To do so, add:

```
...

use CakeDomainManager\DomainController;

...

class AppController extends Controller
{

    ...

    public function initialize()
    {
        ...
        
        DomainController::init($this)->setTemplatePaths();
    }

    ...

}

```

### View

In the view, we have to change the way the elements are getting loaded. In your *AppView.php* you should overwrite the *_getElementFileName()* of the *Cake\View\View* class.

To do that, in *AppView.php*:

```
...

use CakeDomainManager\DomainView;

...

class AppView extends View
{
    ...

    /**
     * @param string $name
     * @param bool $pluginCheck
     * @return false|string
     */
    protected function _getElementFileName($name, $pluginCheck = true)
    {
        return DomainView::init($this)->_getElementFileName($name, $pluginCheck);
    }

    ...
}
```

## Usage

With the settings done you can call elements of the same layer just as before:
```
Request: /users/view/1

<?= $this->element('user_avatar', compact('user')) ?>
```

To call elements from another layer, just use the magical *@* at the end of the traditional CakePHP notation.

Elements of another layer:
```
Request: /pilots/view/1
<?= $this->element('user_avatar@User', ['user' => $pilot->user]) ?>
```

Elements of another sub-layer, e.g. *Domain/Users/Settings*:
```
Request: /pilots/view/1
<?= $this->element('user_avatar@User/Settings', ['user' => $pilot->user]) ?>
```

Elements of another layered plugin:
```
E.g.: plugins/Advertisements/src/Domain/CarRenting/Template/Element/weekly_offers.ctp

Request: /pilots/view/1
<?= $this->element('Advertisements.weekly_offers@CarRenting', ['user' => $pilot->user]) ?>
```

The notation:
```
<?= $this->element('../Users', compact('users)) ?>
```
is no longer supported, or at least it can react impredictably. Instead, organize you structure in a manner, where all reusable templates are located in a domain layers's Element folder. All use the magical *@* notation to call elements from other layers.


## Credits
Juan Pablo Ramirez

[webrider.de](https://webrider.de)

## License

[MIT](https://choosealicense.com/licenses/mit/)