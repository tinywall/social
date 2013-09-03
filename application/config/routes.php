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

$route['default_controller'] = "landing";
$route['404_override'] = '';
//-------------------------------------------------------------------------
$route['api']="api/landing";
$route['api/profile'] = "api/profile/index";
$route['api/(:any)/profile'] = "api/profile/index/$1";
$route['api/(:any)/profile/(:any)'] = "api/profile/$2/$1";
$route['api/profile/(:any)'] = "api/profile/$1";

$route['api/status'] = "api/status/index";
$route['api/(:any)/status'] = "api/status/index/$1";
$route['api/(:any)/status/(:num)'] = "api/status/index/$1/$2";
$route['api/(:any)/status/(:any)'] = "api/status/$2/$1";
$route['api/status/(:any)'] = "api/status/$1";

$route['api/friends'] = "api/connection/friends";
$route['api/(:any)/friends'] = "api/connection/friends/$1";
$route['api/(:any)/friends/(:any)'] = "api/connection/friends/$1/$2";
/*$route['api/(:any)/mutualfriends'] = "api/connection/mutualfriends/$1";
$route['api/(:any)/friendsuggession'] = "api/connection/friendsuggession/$1";
$route['api/followers'] = "api/connection/followers";
$route['api/(:any)/followers'] = "api/connection/followers/$1";
$route['api/followings'] = "api/connection/followings";
$route['api/(:any)/followings'] = "api/connection/followings/$1";
$route['api/connection'] = "api/connection/index";
$route['api/groups'] = "api/connection/groups";
$route['api/followings'] = "api/connection/followings";
$route['api/(:any)/connection'] = "api/connection/index/$1";
$route['api/(:any)/connection/(:any)'] = "api/connection/$2/$1";
$route['api/connection/(:any)'] = "api/connection/$1";*/

$route['api/(:any)']="api/$1";

$route['admin']="admin/landing";
$route['admin/login']="admin/landing/login";
$route['admin/authenticate']="admin/landing/authenticate";
$route['admin/logout']="admin/landing/logout";
$route['admin/(:any)']="admin/$1";

$route['login/(:any)'] = "landing/login/$1";
$route['login'] = "landing/login";
$route['register'] = "landing/register";

$route['test']="test/index";
$route['test/(:any)']="test/$1";

$route['authenticate'] = "landing/authenticate";
$route['logout'] = "landing/logout";
$route['activate/(:any)'] = "landing/activate/$1";
$route['forgot'] = "landing/forgot";
$route['page_visited_log'] = "landing/page_visited_log";
$route['reset'] = "landing/reset";
$route['reset/(:any)'] = "landing/reset/$1";
$route['avatar/(:any)'] = "landing/avatar/$1";
$route['snap/(:any)'] = "landing/snap/$1";
$route['publicsearch'] = "landing/publicsearch";
$route['publicsearch/(:any)'] = "landing/publicsearch/$1";
$route['googleauth'] = "landing/googleauth";
$route['googleauth/(:any)'] = "landing/googleauth/$1";
$route['landing/(:any)'] = "landing/$1";

$route['dashboard'] = "home/dashboard";

$route['home'] = "home/index";
$route['(:any)/home'] = "home/index/$1";
$route['(:any)/home/(:any)'] = "home/$2/$1";
$route['home/(:any)'] = "home/$1";

$route['contacts'] = "contacts/index";
$route['contacts/(:any)'] = "contacts/$1";

$route['profile'] = "profile/index";
$route['(:any)/profile'] = "profile/index/$1";
$route['(:any)/profile/(:any)'] = "profile/$2/$1";
$route['profile/(:any)'] = "profile/$1";

$route['poke'] = "poke/index";
$route['poke/(:any)'] = "poke/$1";
$route['(:any)/poke'] = "poke/index/$1";
$route['(:any)/poke/(:any)'] = "poke/$2/$1";

$route['search'] = "search/index";
$route['search/(:any)'] = "search/$1";

$route['setting'] = "setting/index";
$route['setting/(:any)'] = "setting/$1";

$route['status'] = "status/index";
$route['(:any)/status'] = "status/index/$1";
$route['(:any)/status/(:num)'] = "status/index/$1/$2";
$route['(:any)/status/(:any)'] = "status/$2/$1";
$route['status/(:any)'] = "status/$1";
/*$route['status'] = "status/feeds";
$route['(:any)/status'] = "status/posts/$1";
$route['(:any)/status/(:num)'] = "status/post/$2/$1";
$route['(:any)/status/(:any)'] = "status/$2/$1";
$route['status/(:any)'] = "status/$1";*/

$route['pad'] = "pad/index";
$route['(:any)/pad'] = "pad/index/$1";
$route['(:any)/pad/(:any)'] = "pad/$2/$1";
$route['pad/(:any)'] = "pad/$1";

$route['world'] = "world/index";
$route['(:any)/world'] = "world/index/$1";
$route['(:any)/world/(:any)'] = "world/$2/$1";
$route['world/(:any)'] = "world/$1";

$route['friends'] = "connection/friends";
$route['(:any)/friends'] = "connection/friends/$1";
$route['(:any)/mutualfriends'] = "connection/mutualfriends/$1";
$route['(:any)/friendsuggession'] = "connection/friendsuggession/$1";
$route['followers'] = "connection/followers";
$route['(:any)/followers'] = "connection/followers/$1";
$route['followings'] = "connection/followings";
$route['(:any)/followings'] = "connection/followings/$1";
$route['connection'] = "connection/index";
$route['groups'] = "connection/groups";
$route['followings'] = "connection/followings";
$route['(:any)/connection'] = "connection/index/$1";
$route['(:any)/connection/(:any)'] = "connection/$2/$1";
$route['connection/(:any)'] = "connection/$1";

$route['(:any)/photo/(:num)'] = "album/photo/$1/$2";
$route['album/(:num)'] = "album/snaps/owner/$1";
$route['(:any)/album/(:num)'] = "album/snaps/$1/$2";
$route['album'] = "album/index";
$route['(:any)/album'] = "album/index/$1";
$route['(:any)/album/(:any)'] = "album/$2/$1";
$route['album/(:any)'] = "album/$1";

$route['chatroom'] = "chatroom/index";
$route['chatroom/(:any)'] = "chatroom/$1";

$route['chat'] = "chat/index";
$route['chat/(:any)'] = "chat/$1";

$route['sms'] = "sms/index";
$route['sms/(:any)'] = "sms/$1";

$route['log'] = "log/index";
$route['log/(:any)'] = "log/$1";

$route['(:any)']="home/index/$1";
//-------------------------------------------------------------------------


/* End of file routes.php */
/* Location: ./application/config/routes.php */