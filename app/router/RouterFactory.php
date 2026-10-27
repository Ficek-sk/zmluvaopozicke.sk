<?php

use App\FriendlyUrlRouter;
use Nette\Application\Routers\RouteList;

/**
 * Router factory.
 */
class RouterFactory {

	/**
	 * @return RouteList
	 */

	public function createRouter(\App\Model\SystemModel\LegalCounselQuestion $legalCounselQuestion): RouteList {
		$router = new RouteList();

		$router->addRoute('zmluva-o-pozicke/vzor-<id>', 'Homepage:form');
		$router->addRoute('ako-to-funguje', 'Homepage:process');
		$router->addRoute('o-zmluve-o-pozicke', 'Homepage:about');
		$router->addRoute('vyhody-zmluvy-o-pozicke', 'Homepage:benefits');
		$router->addRoute('vzory-na-stiahnutie', 'Homepage:examples');
		$router->addRoute('odkazy', 'Homepage:links');
		$router->addRoute('napiste-nam', 'Homepage:message');

		$router->addRoute('zmluva-o-pozicke-paragrafove-znenie', 'Homepage:paragraf');
		$router->addRoute('omeskanie-dlznika-pri-zmluve-o-pozicke', 'Homepage:omeskanie');
		$router->addRoute('uroky-pri-zmluve-o-pozicke', 'Homepage:uroky');
		$router->addRoute('judikatura-a-zmluva-o-pozicke', 'Homepage:judikatura');
		$router->addRoute('spotrebitelska-zmluva-o-pozicke', 'Homepage:spotrebitel');
		$router->addRoute('uroky-z-omeskania-pri-zmluve-o-pozicke', 'Homepage:urokyomeskanie');
		$router->addRoute('zmluva-o-pozicke-dohoda-o-splatkach', 'Homepage:dohoda');
		$router->addRoute('rozdiel-medzi-zmluvou-o-pozicke-a-uvere', 'Homepage:rozdiel');
        $router->add(new FriendlyUrlRouter($legalCounselQuestion));

        $router->addRoute('<slug [a-z0-9_-]+>', ['presenter'     => 'Homepage', 'action'        => 'page']);

		$router->addRoute('<presenter>/<action>[/<id>]', 'Homepage:default');


		return $router;
	}
}
