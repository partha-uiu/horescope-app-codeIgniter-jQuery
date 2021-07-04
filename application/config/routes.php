<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

$route['default_controller'] = "home";

/*site route*/
/*$route['login'] = "login/login";
$route['logout'] = "login/logout";
$route['register'] = "register/register";
*/
/*$route['client/logout'] = "client/users/logout";
$route['forgotpassword'] = "users/forgotpassword";
$route['resetpassword/(:any)'] = "users/resetpassword/$1";
*/
//$route['signup/payment/(:any)'] = "users/signupPayment/$1";

// $route['activate/account/(:any)'] = "users/activateAccount/$1";


/* ---------- */
$route['users'] = "login/index";

$route['admin'] = "admin/pusers/login";
$route['admin/logout'] = "admin/pusers/logout";
$route['admin/users/edit/(:any)'] = "admin/users/add/$1";
$route['admin/packages/edit/(:any)'] = "admin/packages/add/$1";
//$route['admin/settings'] = "admin/settings/view";
//
$route['404_override'] = '';


/* End of file routes.php */
/* Location: ./application/config/routes.php */