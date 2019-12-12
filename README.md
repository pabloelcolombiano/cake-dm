# cake-dm
A domain manager for CakePHP applications.

## Introduction

As your CakePHP grows, the respective sizes of your Controller, Model and Template folders increase.

Your *src/Template/Element* folder has probably become a mess too, if you haven't split it in several sub-folders.

New-joiners wonder where to start and, just as you, do not grasp which models relate to another. 

An alternative would be to split everything into plugins. But this might not be what you expect from plugins. You would end up with a quantity of plugins added to the *real* plugins, and a deserted *src* folder. 

Sub-folders! this is the approach proposed by cake-dm. With domain separation in mind, the package proposes to split your MVC structure into layers. And sub-layers.

## Requirements

*CakePHP >= 3.5*

## Installation

With composer, run:

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
        - Component
            GeneralComponent.php
            UserRelatedComponent.php       
        AdminController.php
        AppController.php
        DiscountsController.php
        FlightsController.php
        InvoicesController.php
        MachinesController.php
        PagesController.php
        PilotsController.php
        SeatsController.php       
        StewardsController.php
        TicketsController.php
        UsersController.php
        UserProfilesController.php
    - Model
        - Table
            AppTable.php
            DiscountsTable.php
            FlightsTable.php
        - Entity
        ...
    - Template
        - Admin
        - Discounts
        - Flights
        - Element
            company_logo.ctp
            flight_description.ctp
            user_avatar.ctp            
        - Invoices
        - Layout
            default.ctp
        - Machines
        ...
    - View
        - Cell
        - Helper
        AjaxView.php
        AppView.php
    Application.php
```
### With cake-dm
Cake-dm makes it possible to organize your code as follows, with domain folders marked by arrows:
```
- src     
    - Admin
        - Controller
            AdminController.php
        - Template
            - Layout
                default.ctp 
    - App
        - Console
        - Controller
            - Component
                GeneralComponent.php                   
            - AppController.php
            - PagesController.php
        - Model
        - Template
            - Element
                company_logo.ctp
            - Layout
                default.ctp                            
        - View
            - Cell
            - Helper
            AjaxView.php
            AppView.php               
        Application.php        
    - Invoicing
        - Controller              
            DiscountsController.php
            TicketsController.php
        - Model
        - Template                
    - Logistic
        - Controller
            FlightsController.php
            MachinesController.php
            SeatsController.php
        - Model
        - Template
            - Element
                - Flight
                    flight_description.ctp
            - Flights
            - Machines
            - Seats
    - Staff          
    - User
        - Controller
            - Component             
                UserRelatedComponent.php
            UsersController.php
            UserProfilesController.php
        - Model
            - Table
                UsersTable.php
                UserProfilesTable.php
            - Entity
        - Template
            - Element
                user_avatar.ctp
            - Users
            - UserProfiles
```
The advantages are that:
 - The structure of your app becomes clearer. 
 - The different domains of your application can be clearly assigned to different developers or teams.
 - Template paths pile up. For example here, the layout of all the templates within the layer Admin is different than the others. This can easily be read from the structure.
 - If a template, layout or element is not found in a layer, it will be searched in *App/Template* per default.
 - The domain layer name *App* should actually be your app's namespace, which is *App* by default in CakePHP. For plugins, it should be *Plugin*, regardless of the plugin's name.
 - Elements and Components can be spread throughout the layers according to their relevance.

Note that:
 - It is possible to split your layers in sub-layers, for example by creating a folder *src/Staff/Internal* and a folder *src/Staff/External*. In that case, no MVC folders should be located in the folder *src/Staff*.
 - Plugin structures can be split in the same manner. You should then replace the *App* domain layer by a *Plugin* domain layer.
 - Namespaces do not change. It is therefore forbidden to have two classes with the same name in different layers. More about that below.
 - Cells, Views, Behaviors and Helpers can be distributed along domain layers too, just as the Components.
 
## Settings

The structure is managed by three components.

### Composer

In order to have Cake following the paths without changing the namespaces and intruding in the middleware, you must modify the following in your composer.json file:
```bash
"autoload": {
    "psr-4": {
        "App\\": [           
            "src/Admin",
            "src/App",
            "src/Invoicing",
            "src/Logistic",
            "src/Staff",
            "src/User"
        ],
        ...
    }
},
```

Run
``` composer dump-autoload ```
after any structural change.

The same applies when introducing domain layers in your plugins.

### Controller

In your *AppController.php*, the relevant paths to the templates should be added as early as in the *AppController::initialize()*.

Cake-dm will know in which layer the request was performed. It will include the template paths of that layer, as well as the *App* layer. To do so, add:

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

### Optional 1

Having multiple directories under one same namespace (see [Composer](#composer)), there is a risk that a developer creates a class in a directory, and a different class with the same name in another directory. This is the price of adding a layer to the MVC.

Neither PHP nor Composer will throw any error or warning if this happens.

In order to avoid this, the following test can be added to your *ApplicationTest.php* file, to ensure that all your classes are correctly reached by composer.
```
...

use Cake\Error\FatalErrorException;
use Cake\Log\Log;
use CakeDomainManager\DomainApplication;

...

    /**
     * Makes sure that the domain structure is coherent
     * @throws \ReflectionException
     */
    public function testTrackDuplicatedClassesInNamespaceNoError()
    {
        $error = false;
        try {
            $app = new Application(dirname(dirname(__DIR__)) . '/config');
            DomainApplication::init($app)->trackDuplicatedClassesInNamespace(
                Configure::read('App.namespace', 'App'),
                APP
            );
        } catch (FatalErrorException $exception) {
            Log::error($error = $exception->getMessage());
        }

        $this->assertEquals(false, $error, $error);
    }
...
``` 

Should you absolutely need to have two classes names the same in the *src* folder, there is nothing the present version of the package can do.

### Optional 2

I recommend editing the following line in your *config/app.php* file under the *'App.paths'* entry to:  
```
'templates' => [APP . DS . 'App' . DS . 'Template' . DS],
```
or your app's namespace, if it is not *App*. 

Should you run into any error messages during the installation, Cake will know the path to the Error template.

This can be eventually removed once the migration is completed. The DomainController sets up that path for you, as described in the section *Controller* above. 

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

Elements of another sub-layer, e.g. *User/Settings*:
```
Request: /pilots/view/1
<?= $this->element('user_avatar@User/Settings', ['user' => $pilot->user]) ?>
```

Elements of another layered plugin:
```
E.g.: plugins/Advertisements/src/CarRenting/Template/Element/weekly_offers.ctp

Request: /pilots/view/1
<?= $this->element('Advertisements.weekly_offers@CarRenting', compact('weekly_offers')) ?>
```

The notation:
```
<?= $this->element('../Users', compact('users)) ?>
```
is no longer supported, or at least it can react unpredictably. Instead, organize your structure in a manner, where all reusable templates are located in a domain layer's *Element* folder. And use the magical *@* notation to call elements across domain layers.


## Credits
Juan Pablo Ramirez

[webrider.de](https://webrider.de)

## License

[MIT](https://choosealicense.com/licenses/mit/)
