<?php

use App\FriendlyUrlRouter;
use Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route;

/**
 * Router factory.
 */
class RouterFactory {

	/**
	 * @return Nette\Application\IRouter
	 */

	public function createRouter(\App\Model\SystemModel\LegalCounselQuestion $legalCounselQuestion) {
		$router = new RouteList();

		$router[] = new Route('zmluva-o-pozicke/vzor-<id>', 'Homepage:form');
		$router[] = new Route('ako-to-funguje', 'Homepage:process');
		$router[] = new Route('o-zmluve-o-pozicke', 'Homepage:about');
		$router[] = new Route('vyhody-zmluvy-o-pozicke', 'Homepage:benefits');
		$router[] = new Route('vzory-na-stiahnutie', 'Homepage:examples');
		$router[] = new Route('odkazy', 'Homepage:links');
		$router[] = new Route('napiste-nam', 'Homepage:message');

		$router[] = new Route('zmluva-o-pozicke-paragrafove-znenie', 'Homepage:paragraf');
		$router[] = new Route('omeskanie-dlznika-pri-zmluve-o-pozicke', 'Homepage:omeskanie');
		$router[] = new Route('uroky-pri-zmluve-o-pozicke', 'Homepage:uroky');
		$router[] = new Route('judikatura-a-zmluva-o-pozicke', 'Homepage:judikatura');
		$router[] = new Route('spotrebitelska-zmluva-o-pozicke', 'Homepage:spotrebitel');
		$router[] = new Route('uroky-z-omeskania-pri-zmluve-o-pozicke', 'Homepage:urokyomeskanie');
		$router[] = new Route('zmluva-o-pozicke-dohoda-o-splatkach', 'Homepage:dohoda');
		$router[] = new Route('rozdiel-medzi-zmluvou-o-pozicke-a-uvere', 'Homepage:rozdiel');
        $router[] = new FriendlyUrlRouter($legalCounselQuestion);

        $router[] = new Route('<slug [a-z0-9_-]+>', ['presenter'     => 'Homepage', 'action'        => 'page']);

		$router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');


		return $router;
	}
}
