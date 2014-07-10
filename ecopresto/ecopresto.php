<?php
/**
* NOTICE OF LICENSE
*
* This source file is subject to a commercial license from SARL Ether Création
* Use, copy, modification or distribution of this source file without written
* license agreement from the SARL Ether Création is strictly forbidden.
* In order to obtain a license, please contact us: contact@ethercreation.com
* ...........................................................................
* INFORMATION SUR LA LICENCE D'UTILISATION
*
* L'utilisation de ce fichier source est soumise a une licence commerciale
* concedee par la societe Ether Création
* Toute utilisation, reproduction, modification ou distribution du present
* fichier source sans contrat de licence ecrit de la part de la SARL Ether Création est
* expressement interdite.
* Pour obtenir une licence, veuillez contacter la SARL Ether Création a l'adresse: contact@ethercreation.com
* ...........................................................................
*
*  @package ec_ecopresto
*  @author Arthur Revenaz
*  @copyright Copyright (c) 2010-2014 S.A.R.L Ether Création (http://www.ethercreation.com)
*  @license Commercial license
*/

require_once dirname(__FILE__).'/class/catalog.class.php';
require_once dirname(__FILE__).'/class/reference.class.php';

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class ecopresto extends Module{
	private $_html = '';
	private $_postErrors = array();
	const INSTALL_SQL_FILE = 'create.sql';
	const UNINSTALL_SQL_FILE = 'drop.sql';


	public function __construct()
	{
		$this->name = 'ecopresto';
		$this->tab = 'others';
		$this->version = 2.8.1;
		$this->need_instance = 0;
		$this->author = 'Ether Création';
		$this->displayName = $this->l('Drop shipping - Ecopresto');
		$this->description = $this->l('Importer vos produits en Drop shipping avec Ecopresto');
		$this->confirmUninstall = $this->l('Etes vous sur de vouloir désinstaller le module ?');

		parent::__construct();
	}

	public function install()
	{
		if (!$this->executeSQLFile(self::INSTALL_SQL_FILE) || !parent::install())
			return false;

		$catalog = new Catalog();
		if (!$catalog->SetSupplier())
			return false;
		if (!$catalog->SetTax())
			return false;
		if (!$catalog->SetLang())
			return false;

		self::updateInfoEco('ECO_TOKEN', md5(time()._COOKIE_KEY_));
		Configuration::updateValue('ECOPRESTO_DEMO', 0);
		return true;
	}

	public function uninstall()
	{
		return $this->executeSQLFile(self::UNINSTALL_SQL_FILE) && parent::uninstall();
	}

	public function getInfoEco($name)
	{
		return Db::getInstance()->getValue('SELECT `value` FROM `'._DB_PREFIX_.'ec_ecopresto_info` WHERE name="'.pSQL($name).'"');
	}

	public function updateInfoEco($name, $value)
	{
		return Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'ec_ecopresto_info` SET `value` = "'.pSQL($value).'" WHERE `name`="'.pSQL($name).'"');
	}

	public function executeSQLFile($file)
	{
		$path = realpath(_PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$this->name).DIRECTORY_SEPARATOR.'sql/';

		if (!file_exists($path.$file))
			return false;
		else if (!$sql = Tools::file_get_contents($path.$file))
				return false;

			$sql = preg_split("/;\s*[\r\n]+/", str_replace('PREFIX_', _DB_PREFIX_, $sql));

		foreach ($sql as $query)
		{
			$query = trim($query);
			if ($query)
				if (!Db::getInstance()->Execute($query))
				{
					$this->_postErrors[] = Db::getInstance()->getMsgError().' '.$query;
					return false;
				}
		}
		return true;
	}

	private function controleLicence($id_eco, $typ)
	{
		$res = Tools::file_get_contents(self::getInfoEco('ECO_URL_LIC').$id_eco);

		if (strpos($res, '#error') !== false)
			return $res;
		elseif ($typ == 1)
			return $res;
		else
			return true;
	}

	public function getContent()
	{
		$catalog = new Catalog();

		if (version_compare(_PS_VERSION_, '1.5', '>='))
		{
			if ('http://'.$_SERVER['HTTP_HOST'] != Tools::getShopDomain(true, true))
			{
				header('Location: '.Tools::getShopDomain(true, true).$_SERVER['REQUEST_URI']);
				exit();
			}
		}

		if (version_compare(_PS_VERSION_, '1.5', '>='))
		{
			self::updateInfoEco('ID_SHOP', $this->context->shop->id);
			self::updateInfoEco('ID_LANG', $this->context->language->id);
		}
		else
		{
			global $cookie;
			self::updateInfoEco('ID_SHOP', 1);
			self::updateInfoEco('ID_LANG', $cookie->id_lang);
		}

		$catalog->SetConfig();

		$output = '<div class="toolbarBox toolbarHead">
						<div class="pageTitle">
							<h3>
								<span id="current_obj" class="fontNa">
									<span class="breadcrumb item-0 ">Modules > </span>
									<span class="breadcrumb item-1 ">'.$this->displayName.'</span>
								</span>
							</h3>
						</div>
					</div>';

		if (Tools::isSubmit('maj_tax'))
		{
			$catalog->updateTax();
			$output .= $this->displayConfirmation($this->l('Tax mise à jour'));
		}
		if (Tools::isSubmit('maj_lang'))
		{
			$catalog->updateLang();
			$output .= $this->displayConfirmation($this->l('Langue mise à jour'));
		}
		if (Tools::isSubmit('maj_config'))
		{
			$catalog->updateConfig();
			$output .= $this->displayConfirmation($this->l('Paramètre mise à jour'));
		}
		if (Tools::isSubmit('maj_attributes'))
		{
			$catalog->updateAttributes();
			$output .= $this->displayConfirmation($this->l('Attributs mise à jour'));
		}

		return $output.$this->displayForm();
	}


	public function displayForm()
	{
		$html = '';
		$catalog = new Catalog();
		$licence = self::controleLicence($catalog->tabConfig['ID_ECOPRESTO'], 0);
                
		if ($licence !== true)
		{
			Configuration::updateValue('ECOPRESTO_DEMO', 0);
			$html .= $this->displayError($licence.$catalog->tabConfig['ID_ECOPRESTO']);
		}            

		$tabLic = explode(';', self::controleLicence($catalog->tabConfig['ID_ECOPRESTO'], 1));
		$nbTot = Db::getInstance()->getValue('SELECT count(distinct(`supplier_reference`)) FROM  `'._DB_PREFIX_.'product` p, `'._DB_PREFIX_.'ec_ecopresto_product_shop` ps WHERE p.`supplier_reference` = ps.`reference`');


		$html .= '<input type="hidden" name="idshop" value="'.(int)self::getInfoEco('ID_SHOP').'" id="idshop" />';
		$html .= '<input type="hidden" name="ec_token" value="'.self::getInfoEco('ECO_TOKEN').'" id="ec_token" />';
		$html .= '<script type="text/javascript">
			var textImportCatalogueEnCours = "'.$this->l('Import catalogue en cours...').'";
			var textImportCatalogueTermine = "'.$this->l('Import catalogue terminé avec succès').'";
			var textImportCatalogueErreur = "'.$this->l('Import catalogue non terminé : Erreur').'";
			var textMAJProduitsEnCours = "'.$this->l('Mise à jour des produits en cours...').'";
			var textMAJProduitsTermine = "'.$this->l('Mise à jour des produits terminé avec succès').'";
			var textSynchroEnCours = "'.$this->l('Synchronisation en cours...').'";
			var textSynchroTermine = "'.$this->l('Synchronisation terminée avec succès').'";
			var textSynchroErreur = "'.$this->l('Synchronisation non terminée : Erreur').'";
			var textDerefEnCours = "'.$this->l('Récupération des données articles déréférencés...').'";
			var textDerefTermine = "'.$this->l('Récupération des produits déréférencés terminée.').'";

		</script>';
		$html .= '<script type="text/javascript" src="../modules/ecopresto/js/tablefilter.js"></script>';
		$html .= '<script src="../modules/ecopresto/js/TFExt_ColsVisibility/TFExt_ColsVisibility.js" language="javascript" type="text/javascript"></script>';
		$html .= '<script src="../modules/ecopresto/js/XHRConnection.js"></script>';
		$html .= '<script src="../modules/ecopresto/js/function.js"></script>';
		$html .= '<link href="../modules/ecopresto/css/ec_ecopresto.css" rel="stylesheet">';
		$html .= '';

		$html .= '<div id="loading-div-background">
						<div id="loading-div" class="ui-corner-all" >
							<div class="progress progress-striped active well">

								<div class="bar barzeo"></div>

								<table class="barcent">
								<tr>
								<td>
								<div class="pull-right" id="pourcentage"><center>0%</center></div>
								</td>
								</tr>
								</table>
							</div>
							<h2 id="h2Modal" class="colgrfon">'.$this->l('Veuillez patienter').'</h2>
							<p id="titreModal">'.$this->l('Import catalogue en cours....').'</p>
							<p id="titreModalFin">'.$this->l('Import réalisé avec succès').'</p>
							<p id="titreModalErreur">'.$this->l('Erreur durant l\'import').'</p>
							<p id="closeModal"><a href="#" id="closeModalButton">'.$this->l('Fermer').'</a></p>
							<p id="closeModalWithoutReload"><a href="#" id="closeModalWithoutReloadButton">'.$this->l('Fermer').'</a></p>
						</div>
					</div>';

		if (Configuration::get('ECOPRESTO_DEMO') == 0 || $licence !== true)
		{
			$html .= '<h2>'.$this->l('Bienvenue sur le module de dropshipping Ecopresto !.').'<br /><br />'.$this->l('Choisissez une parmi les 3 options ci-dessous et entrez dans l\'univers Ecopresto. N\'oubliez pas de prendre le temps de lire attentivement la documentation du module ! Merci.').'<br /><br />'.
									$this->l('L\'équipe d\'Ecopresto.').'</h2>';

			$html .= '<div id="menuTab1" class="selected">';

			$html .= '<table class="barcent" cellspacing="10px">
				<tr>
					<td class="bartrnto">
						<div id="cone_1" class="picto_preac">
							<img src="http://'.Tools::getShopDomainSsl().__PS_BASE_URI__.'modules/ecopresto/img/connect.png" />
						</div>
					</td>
					<td class="bartrntqc">
						<div id="cont_1">
							<img src="http://'.Tools::getShopDomainSsl().__PS_BASE_URI__.'modules/ecopresto/img/femme.png" height="171px" /><br />
							
							
						</div>
					</td>
					<td class="bartrntoc">
						<div id="test_1">
							
							<img src="http://'.Tools::getShopDomainSsl().__PS_BASE_URI__.'modules/ecopresto/img/homme.png" />
							
						</div>
					</td>
				</tr>
				<tr>
					<td valign="top"><h3>'.$this->l('Vous avez déjà un compte? Enregistrez le ici :').'</h3>
					</td>
					<td valign="top"><h3>'.$this->l('Vous n\'avez pas de compte? Contactez Ecopresto !').'</h3>
					</td>
					<td valign="top"><h3>'.$this->l('Vous souhaitez le tester gratuitement? Cliquez sur le bouton DEMO !').'</h3>
					</td>
				</tr>
				<tr>
					<td>
					<form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" name="form_config" method="post">
									<p>ID EcoPresto : <input type="text" name="CONFIG_ECO[ID_ECOPRESTO]" value="" /></p>
									<input type="submit" name="maj_config" value="'.$this->l('Activer').'" class="okpreac button"/>
							   </form>
					</td>
					<td><img src="http://'.Tools::getShopDomainSsl().__PS_BASE_URI__.'modules/ecopresto/img/contact.jpg" class="contactpreac"/>
					</td>
					<td><img src="http://'.Tools::getShopDomainSsl().__PS_BASE_URI__.'modules/ecopresto/img/demo.png" class="demopreac"/>
					</td>
				</tr>
				</table>';
			$html .= '</div>';
		}

		if (Configuration::get('ECOPRESTO_DEMO') != 0 && $licence === true)
		{
			$html .= '<div id="menuTab1" class="selected">
						
						<div class="picto_ac">
							<img src="http://'.Tools::getShopDomainSsl().__PS_BASE_URI__.'modules/ecopresto/img/news.png" id="menuTab12" class="menuTabButton"/>
							<div class="text_preac">
								<h3 id="menuTab12" class="menuTabButton">'.$this->l('Actualités Ecopresto').'</h3>
							</div>
						</div>
						<div class="picto_ac">
							<img src="http://'.Tools::getShopDomainSsl().__PS_BASE_URI__.'modules/ecopresto/img/catalogue.png" id="menuTab2" class="menuTabButton"/>
							<div class="text_preac">
								<h3 id="menuTab2" class="menuTabButton">'.$this->l('Catalogue Ecopresto').'</h3>
							</div>
						</div>
						<div class="picto_ac">
							<img src="http://'.Tools::getShopDomainSsl().__PS_BASE_URI__.'modules/ecopresto/img/comande.png" id="menuTab10" class="menuTabButton"/>
							<div class="text_preac">
								<h3 id="menuTab10" class="menuTabButton">'.$this->l('Commande(s) en attente').'</h3>
							</div>
						</div>
						<div class="picto_ac">
							<img src="http://'.Tools::getShopDomainSsl().__PS_BASE_URI__.'modules/ecopresto/img/tracking.png" id="menuTab11" class="menuTabButton"/>
							<div class="text_preac">
								<h3 id="menuTab11" class="menuTabButton">'.$this->l('Suivi(s) de commande').'</h3>
							</div>
						</div>
						<div class="picto_ac">
							<img src="http://'.Tools::getShopDomainSsl().__PS_BASE_URI__.'modules/ecopresto/img/dereference.png" id="menuTab9" class="menuTabButton"/>
							<div class="text_preac">
								<h3 id="menuTab9" class="menuTabButton">'.$this->l('Produits supprimés').'</h3>
							</div>
						</div>
						<div class="picto_ac">
							<img src="http://'.Tools::getShopDomainSsl().__PS_BASE_URI__.'modules/ecopresto/img/parametrage.png" id="menuTab6" class="menuTabButton"/>
							<div class="text_preac">
								<h3 id="menuTab6" class="menuTabButton">'.$this->l('Réglage module').'</h3>
							</div>
						</div>
					</div>';
		}

		$html .= '<div id="tabList">';

		$html .= '<div id="menuTab2Sheet" class="tabItem">';

		$cat = $sscat = '';
		$ncat = $sscat = -1;
        $nsscat = 0;

		$all_catalog = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT `category_1`, `ss_category_1`, `name_1`, `category_1`, `manufacturer`, `reference`, `price`, `pmvc`
																		FROM `'._DB_PREFIX_.'ec_ecopresto_catalog`
																	   ORDER BY `category_1`, `ss_category_1`,`name_1`');

		$all_selection = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT `reference`, `id_shop`
																		FROM `'._DB_PREFIX_.'ec_ecopresto_product_shop`
																		WHERE `imported`=0 AND `id_shop`='.(int)self::getInfoEco('ID_SHOP'));
		$pdt_sel = array();
		foreach ($all_selection as $selection)
			$pdt_sel[$selection['reference']] = ($selection['id_shop'] == self::getInfoEco('ID_SHOP')?1:'');

		$prestashopCategories = Category::getCategories((int)self::getInfoEco('ID_LANG'), false);
		$lstdercateg = $catalog->getCategory($prestashopCategories, $prestashopCategories[0][1], 1, 0);

		if ($all_catalog)
		{
			$html .= '<span id="spnColMng"></span><div id="colsMng"></div>';

			$html .= '<form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" name="form_catalogue" method="post">
						<table class="table" id="table1" cellspacing="0" cellpadding="0">
							<thead>
								<tr>
									<th><input type="checkbox" class="cbImporterAll" name="Importer" value="'.$this->l('Importer').'"></th>
									<th>'.$this->l('Catégorie Ecopresto').'</th>
									<th>'.$this->l('Sous catégorie Ecopresto').'</th>
									<th>'.$this->l('Catégorie locale').'</th>
									<th>'.$this->l('Référence').'</th>
									<th>'.$this->l('Produit').'</th>
									<th>'.$this->l('Marque').'</th>
									<th>'.$this->l('Prix HT').'</th>
									<th>'.$this->l('Prix de vente moyen HT').'</th>
									<th>'.$this->l('Marge').'</th>
								 </tr>
							</thead>
							<tbody>';
			foreach ($all_catalog as $resu)
			{
				$catSelected = $ssCatSelected = '';

				if ($resu['category_1'] != $cat)
				{
					$catSelected = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_category` FROM `'._DB_PREFIX_.'ec_ecopresto_category_shop` WHERE `name`="'.pSQL(base64_encode($resu['category_1'])).'" AND `id_shop`='.(int)self::getInfoEco('ID_SHOP'));
					$ncat++;
					$nsscat = -1;
					$html .= '<tr id='.$ncat.' class="row_hover">
									<td>
										<input type="checkbox" id="check'.$ncat.'" name="check'.$ncat.'" value="'.$ncat.'" class="checBB" />
									</td>
									<td class="cat cat'.$ncat.' curpoin"><span class="catdisplay">'.Tools::safeOutput($resu['category_1']).'</span></td>
									<td></td>
									<td>
										<span class="spancat '.($catSelected?' dnone ':'').'">
											'.$this->l('Créer automatiquement').'
											<img width="16" height="16" alt="edit" class="cuver" src="'._PS_ADMIN_IMG_.'edit.gif" class="imgcategorie" rel="'.base64_encode($resu['category_1']).'">
										</span>
										<select catSel="'.($catSelected > 0?$catSelected:0).'" name="catPS" class="selSpe '.(!$catSelected?' dnone ':'').'" rel="'.base64_encode($resu['category_1']).'">
												<option value="0">'.$this->l('Créer automatiquement').'</option>'.
						$lstdercateg
						.'</select>

									</td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
								 </tr>';
					$cat = $resu['category_1'];
				}

				if ($resu['ss_category_1'] != $sscat)
				{
					$ssCatSelected = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_category` FROM `'._DB_PREFIX_.'ec_ecopresto_category_shop` WHERE `name`="'.pSQL(base64_encode($resu['ss_category_1'])).'" AND `id_shop`='.(int)self::getInfoEco('ID_SHOP'));
					$nsscat++;

					$html .= '<tr class="row_hover dnone" id='.$ncat.'___'.$nsscat.'>
									<td>
										<input type="checkbox" id="check'.$ncat.'___'.$nsscat.'" name="check'.$ncat.'___'.$nsscat.'" value="'.$ncat.'___'.$nsscat.'" class="checBB checBB'.$ncat.'" />
									</td>
									<td class="ssceza"><span class="catdisplay2">'.Tools::safeOutput($resu['category_1']).'</span></td>
									<td class="sscat sscat'.$ncat.' nsscat'.$nsscat.' curpoin">'.Tools::safeOutput($resu['ss_category_1']).'</td>
									<td>
										<span class="spancat '.($ssCatSelected?' dnone ':'').'">
											'.$this->l('Créer automatiquement').'
											<img width="16" height="16" alt="edit" class="cuver" src="'._PS_ADMIN_IMG_.'edit.gif" class="imgcategorie" rel="'.base64_encode($resu['ss_category_1']).'">
										</span>
										<select catSel="'.($ssCatSelected > 0?$ssCatSelected:0).'" name="catPS" class="selSpe '.(!$ssCatSelected?' dnone ':'').'" rel="'.base64_encode($resu['ss_category_1']).'">
												<option value="0">'.$this->l('Créer automatiquement').'</option>'.
						$lstdercateg
						.'</select>
									</td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
								</tr>';
					$sscat = $resu['ss_category_1'];
				}

				$html .= '<tr class="row_hover display_tr" id='.$ncat.'___'.$nsscat.'___'.Tools::safeOutput($resu['reference']).'>
									<td><input type="checkbox" id="check'.$ncat.'___'.$nsscat.'___'.Tools::safeOutput($resu['reference']).'" '.(isset($pdt_sel[$resu['reference']])?'checked="checked"':'').' rel="'.Tools::safeOutput($resu['reference']).'" name="check'.Tools::safeOutput($resu['reference']).'" value="'.$ncat.'___'.$nsscat.'___'.Tools::safeOutput($resu['reference']).'" class="checBB checBB'.$ncat.' checBB'.$ncat.'___'.$nsscat.' pdtI"></td>
									<td class="ssceza"><span class="catdisplay3">'.Tools::safeOutput($resu['category_1']).'</span></td>
									<td class="ssceza"><span class="sscatdisplay3">'.Tools::safeOutput($resu['ss_category_1']).'</span></td>
									<td><span class="catLoc'.Tools::safeOutput($resu['reference']).' dnone">'.base64_encode(Tools::safeOutput($resu['ss_category_1'])).'</span></td>
									<td>'.Tools::safeOutput($resu['reference']).'</td>
									<td class="pdt pdtcat'.$ncat.' pdtnsscat'.$nsscat.'sscat'.$ncat.'">'.Tools::safeOutput($resu['name_1']).'</td>
									<td>'.Tools::safeOutput($resu['manufacturer']).'</td>
									<td>'.Tools::safeOutput($resu['price']).'€</td>
									<td>'.Tools::safeOutput($resu['pmvc']).'€</td>
									<td>'.($resu['price'] > 0?round((($resu['pmvc'] - $resu['price']) / $resu['price'] * 100), 2):'?').'%</td>
							</tr>';
			}
			$html .= '</tbody>';
			$html .= '</table>';
			$html .= '</form>';
			$html .= '<script>
					   catSelSpeAfter();
					</script>';
						$html .= '<p class="spealer"><b>'.$this->l('Produits autorisés : ').'<span class="totAuth">'.$nbTot.'</span>/<span class="totAuthMax">'.$tabLic[2].'</span></b><p>';
		}
		$html .= '<p><input id="importCat" type="submit" class="button" name="OK" value="'.$this->l('1 - Rafraichir le catalogue proposé par Ecopresto').'" onclick="javascript:GetFilecsv();" /></p>';
		if ($all_catalog)
            $html .= '<p><input type="submit" class="button" name="OK"  id="validSelect" value="'.$this->l('2 - Enregistrer la sélection de produits').'" onclick="javascript:MAJProduct();" /></p>';
        $html .= '<p><input type="button" onclick="javascript:recupInfoMajPS(1)" value="'.$this->l('3 - Importer la selection dans sa boutique').'" class="button" /></p>';
		$html .= '</div>';

		$html .= '<div id="menuTab6Sheet" class="tabItem">';
		$html .= '<form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" name="form_config" method="post">';

		$html .= '<h3>'.$this->l('Cron :').'</h3>';
				$html .= '<p>'.$this->l('Stock : ').'http://'.Tools::getShopDomainSsl().__PS_BASE_URI__.'modules/ecopresto/stock.php?ec_token='.Tools::safeOutput(self::getInfoEco('ECO_TOKEN')).'</p>';
				$html .= '<p>'.$this->l('Commande : ').'http://'.Tools::getShopDomainSsl().__PS_BASE_URI__.'modules/ecopresto/gen_com.php?ec_token='.Tools::safeOutput(self::getInfoEco('ECO_TOKEN')).'</p>';
				$html .= '<p>'.$this->l('Tracking : ').'http://'.Tools::getShopDomainSsl().__PS_BASE_URI__.'modules/ecopresto/tracking.php?ec_token='.Tools::safeOutput(self::getInfoEco('ECO_TOKEN')).'</p>';

		$html .= '<h3>'.$this->l('Prix').'</h3>';
		$html .= '<p>'.$this->l('Importer les prix d\'achat :').'
					<input type="radio" name="CONFIG_ECO[PA_TAX]" value="1" '.(($catalog->tabConfig['PA_TAX'] == 1)?'checked=checked':'').' /> '.$this->l('HT').'
					<input type="radio" name="CONFIG_ECO[PA_TAX]" value="0" '.(($catalog->tabConfig['PA_TAX'] == 0)?'checked=checked':'').' /> '.$this->l('TTC').'
				</p>';
		$html .= '<p>'.$this->l('Prix de vente généralement constaté : ').'
					<input type="radio" name="CONFIG_ECO[PMVC_TAX]" value="1" '.(($catalog->tabConfig['PMVC_TAX'] == 1)?'checked=checked':'').' /> '.$this->l('HT').'
					<input type="radio" name="CONFIG_ECO[PMVC_TAX]" value="0" '.(($catalog->tabConfig['PMVC_TAX'] == 0)?'checked=checked':'').' /> '.$this->l('TTC').'
				</p>';

		$html .= '<h3>'.$this->l('Paramètres autres').'</h3>';

		$html .= '<p>'.$this->l('Mettre à jour les prix de vente généralement constaté : ').'
					<input type="radio" name="CONFIG_ECO[UPDATE_PRICE]" value="1" '.(($catalog->tabConfig['UPDATE_PRICE'] == 1)?'checked=checked':'').' /> <img title="'.$this->l('Oui').'" alt="'.$this->l('Oui').'" src="../img/admin/enabled.gif">
					<input type="radio" name="CONFIG_ECO[UPDATE_PRICE]" value="0" '.(($catalog->tabConfig['UPDATE_PRICE'] == 0)?'checked=checked':'').' /> <img title="'.$this->l('Non').'" alt="'.$this->l('Non').'" src="../img/admin/disabled.gif">
				</p>';
		$html .= '<p>'.$this->l('Mettre à jour les EAN : ').'
					<input type="radio" name="CONFIG_ECO[UPDATE_EAN]" value="1" '.(($catalog->tabConfig['UPDATE_EAN'] == 1)?'checked=checked':'').' /> <img title="'.$this->l('Oui').'" alt="'.$this->l('Oui').'" src="../img/admin/enabled.gif">
					<input type="radio" name="CONFIG_ECO[UPDATE_EAN]" value="0" '.(($catalog->tabConfig['UPDATE_EAN'] == 0)?'checked=checked':'').' /> <img title="'.$this->l('Non').'" alt="'.$this->l('Non').'" src="../img/admin/disabled.gif">
				</p>';
		$html .= '<p>'.$this->l('Mettre à jour les noms et descriptions : ').'
					<input type="radio" name="CONFIG_ECO[UPDATE_NAME_DESCRIPTION]" value="0" '.(($catalog->tabConfig['UPDATE_NAME_DESCRIPTION'] == 0)?'checked=checked':'').' /> '.$this->l('Aucun').'
					<input type="radio" name="CONFIG_ECO[UPDATE_NAME_DESCRIPTION]" value="1" '.(($catalog->tabConfig['UPDATE_NAME_DESCRIPTION'] == 1)?'checked=checked':'').' /> '.$this->l('Juste les noms de produits').'
					<input type="radio" name="CONFIG_ECO[UPDATE_NAME_DESCRIPTION]" value="2" '.(($catalog->tabConfig['UPDATE_NAME_DESCRIPTION'] == 2)?'checked=checked':'').' /> '.$this->l('Juste les descrpitions de produits').'
					<input type="radio" name="CONFIG_ECO[UPDATE_NAME_DESCRIPTION]" value="3" '.(($catalog->tabConfig['UPDATE_NAME_DESCRIPTION'] == 3)?'checked=checked':'').' /> '.$this->l('Les deux').'
				</p>';
		$html .= '<p>'.$this->l('Mettre à jour les images : ').'
					<input type="radio" name="CONFIG_ECO[UPDATE_IMAGE]" value="1" '.(($catalog->tabConfig['UPDATE_IMAGE'] == 1)?'checked=checked':'').' /> <img title="'.$this->l('Oui').'" alt="'.$this->l('Oui').'" src="../img/admin/enabled.gif">
					<input type="radio" name="CONFIG_ECO[UPDATE_IMAGE]" value="0" '.(($catalog->tabConfig['UPDATE_IMAGE'] == 0)?'checked=checked':'').' /> <img title="'.$this->l('Non').'" alt="'.$this->l('Non').'" src="../img/admin/disabled.gif">
				</p>';
		$html .= '<p>'.$this->l('Supprimer les produits n’apparaissant plus dans le catalogue Ecopresto : ').'
					<input type="radio" name="CONFIG_ECO[UPDATE_PRODUCT]" value="1" '.(($catalog->tabConfig['UPDATE_PRODUCT'] == 1)?'checked=checked':'').' /> <img title="'.$this->l('Oui').'" alt="'.$this->l('Oui').'" src="../img/admin/enabled.gif">
					<input type="radio" name="CONFIG_ECO[UPDATE_PRODUCT]" value="0" '.(($catalog->tabConfig['UPDATE_PRODUCT'] == 0)?'checked=checked':'').' /> <img title="'.$this->l('Non').'" alt="'.$this->l('Non').'" src="../img/admin/disabled.gif">
				</p>';
		$html .= '<p>'.$this->l('Indexer les produits pour la recherche : ').'
					<input type="radio" name="CONFIG_ECO[PARAM_INDEX]" value="1" '.(($catalog->tabConfig['PARAM_INDEX'] == 1)?'checked=checked':'').' /> <img title="'.$this->l('Oui').'" alt="'.$this->l('Oui').'" src="../img/admin/enabled.gif">
					<input type="radio" name="CONFIG_ECO[PARAM_INDEX]" value="0" '.(($catalog->tabConfig['PARAM_INDEX'] == 0)?'checked=checked':'').' /> <img title="'.$this->l('Non').'" alt="'.$this->l('Non').'" src="../img/admin/disabled.gif">
				</p>';
		$html .= '<p>'.$this->l('Statut import de nouveaux produits : ').'
					<input type="radio" name="CONFIG_ECO[PARAM_NEWPRODUCT]" value="1" '.(($catalog->tabConfig['PARAM_NEWPRODUCT'] == 1)?'checked=checked':'').' /> '.$this->l('Actif').'
					<input type="radio" name="CONFIG_ECO[PARAM_NEWPRODUCT]" value="0" '.(($catalog->tabConfig['PARAM_NEWPRODUCT'] == 0)?'checked=checked':'').' /> '.$this->l('Désactivé').'
				</p>';
		$html .= '<p>'.$this->l('Mettre a jour seulement les nouveaux produits : ').'
					<input type="radio" name="CONFIG_ECO[PARAM_MAJ_NEWPRODUCT]" value="1" '.(($catalog->tabConfig['PARAM_MAJ_NEWPRODUCT'] == 1)?'checked=checked':'').' /> '.$this->l('Actif').'
					<input type="radio" name="CONFIG_ECO[PARAM_MAJ_NEWPRODUCT]" value="0" '.(($catalog->tabConfig['PARAM_MAJ_NEWPRODUCT'] == 0)?'checked=checked':'').' /> '.$this->l('Désactivé').'
				</p>';
		$html .= '<p>'.$this->l('Remontée de commande : ').'
					<input type="radio" name="CONFIG_ECO[IMPORT_AUTO]" value="1" '.(($catalog->tabConfig['IMPORT_AUTO'] == 1)?'checked=checked':'').' /> '.$this->l('Automatique').'
					<input type="radio" name="CONFIG_ECO[IMPORT_AUTO]" value="0" '.(($catalog->tabConfig['IMPORT_AUTO'] == 0)?'checked=checked':'').' /> '.$this->l('Manuelle').'
				</p>';

		$html .= '<p><input type="submit" class="button" name="maj_config" value="'.$this->l('Enregistrer').'" /></p>';
		$html .= '</form>';

		$html .= '<h3>'.$this->l('Parametrage multilangue').'</h3>';
		$html .= '<form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" name="form_lang" method="post">';
		$html .= $catalog->getAllLang();
		$html .= '<p><input type="submit" class="button" name="maj_lang" value="'.$this->l('Mise à jour multilangue').'" /></p>';
		$html .= '</form>';

		$html .= '<h3>'.$this->l('Parametrage Taxe').'</h3>';
		$html .= '<form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" name="form_focus" method="post">';
		$html .= $catalog->getAllTax();
		$html .= '<p><input type="submit" class="button" name="maj_tax" value="'.$this->l('Mise à jour taxe').'" /></p>';
		$html .= '</form>';

		$html .= '<h3>'.$this->l('Paramètrage attribut').'</h3>';
		$html .= '<form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" name="form_attributes" method="post">';
		$html .= $catalog->getAllAttributes();
		$html .= '<p><input type="submit" class="button" name="maj_attributes" value="'.$this->l('Mise à jour attribut').'" /></p>';
		$html .= '</form>';

		$html .= '</div>';

		$html .= '<div id="menuTab12Sheet" class="tabItem">';
		$html .= '<iframe src="'.Tools::safeOutput(self::getInfoEco('ECO_URL_ACTU')).Tools::safeOutput($catalog->tabConfig['ID_ECOPRESTO']).'" class="barcent" ></iframe>';
		$html .= '</div>';

		$html .= '<div id="menuTab10Sheet" class="tabItem">';
		$commande = $catalog->getOrders(0);

		if (isset($commande) && count($commande) > 0)
		{
			$dossAdmin = explode('/index.php?', $_SERVER['REQUEST_URI']);
			$dossAdmin = explode('/', $dossAdmin[0]);
			$dossAdmin = $dossAdmin[count($dossAdmin) - 1];

			$html .= '<table id="list_order" class="table">';
			$html .= '<tr>';
			$html .= '<th>'.$this->l('ID commande').'</th>';
			$html .= '<th>'.$this->l('Date').'</th>';
			$html .= '<th>'.$this->l('Voir').'</th>';
			$html .= '<th>'.$this->l('Envoyer ecopresto').'</th>';
			$html .= '<th>'.$this->l('Ne pas envoyer').'</th>';
			$html .= '</tr>';

			foreach ($commande as $com)
			{
				$html .= '<tr id="orderMan'.$com['id_order'].'">';
				$html .= '<td>'.$com['id_order'].'</td>';
				$html .= '<td>'.$com['DatI'].'</td>';

				$html .= '<td><a target="_blank" href="'.__PS_BASE_URI__.$dossAdmin.'/index.php?'.(version_compare(_PS_VERSION_, '1.5', '<')?'tab':'controller').'=AdminOrders&id_order='.Tools::safeOutput($com['id_order']).'&vieworder&ec_token='.self::getInfoEco('ECO_TOKEN').'&token='.Tools::getAdminTokenLite('AdminOrders').'">Voir</a></td>';
				$html .= '<td><img src="'._PS_ADMIN_IMG_.'enabled.gif" class="sendCom" rel="'.$com['id_order'].'" /></td>';
				$html .= '<td><img src="'._PS_ADMIN_IMG_.'disabled.gif" class="NoSendCom" rel="'.$com['id_order'].'" /></td>';
				$html .= '</tr>';
			}
			$html .= '</table>';
		}
		else
			$html .= $this->l('Aucune commande en attente');

		$html .= '</div>';

		$html .= '<div id="menuTab11Sheet" class="tabItem">';
		$tracking = $catalog->getTracking();

		if (isset($tracking) && count($tracking) > 0)
		{
			$dossAdmin = explode('/index.php?', $_SERVER['REQUEST_URI']);
			$dossAdmin = explode('/', $dossAdmin[0]);
			$dossAdmin = $dossAdmin[count($dossAdmin) - 1];

			$html .= '<table id="list_order"  class="table">';
			$html .= '<tr>';
			$html .= '<th>'.$this->l('ID commande').'</th>';
			$html .= '<th>'.$this->l('Date exp').'</th>';
			$html .= '<th>'.$this->l('Numéro de tracking').'</th>';
			$html .= '<th>'.$this->l('Mode de tracking').'</th>';
			$html .= '<th>'.$this->l('Url de tracking').'</th>';
			$html .= '<th>'.$this->l('Voir la commande').'</th>';
			$html .= '</tr>';

			foreach ($tracking as $track)
			{
				$html .= '<tr id="orderTrack'.$track['id_order'].'">';
				$html .= '<td>'.$track['id_order'].'</td>';
				$html .= '<td>'.date('d/m/Y', $track['date_exp']).'</td>';
				$html .= '<td>'.Tools::safeOutput($track['numero']).'</td>';
				$html .= '<td>'.Tools::safeOutput($track['transport']).'</td>';
				$html .= '<td><a href="'.Tools::safeOutput($track['url_exp']).'" target="_blank">'.Tools::safeOutput($track['url_exp']).'</a></td>';
				$html .= '<td><a target="_blank" href="'.__PS_BASE_URI__.$dossAdmin.'/index.php?'.(version_compare(_PS_VERSION_, '1.5', '<')?'tab':'controller').'=AdminOrders&id_order='.Tools::safeOutput($track['id_order']).'&vieworder&token='.Tools::getAdminTokenLite('AdminOrders').'">Voir</a></td>';
				$html .= '</tr>';
			}
			$html .= '</table>';
		}
		else
			$html .= $this->l('Aucun tracking depuis 30 jours');

		$html .= '</div>';

		$html .= '<div id="menuTab9Sheet" class="tabItem">';
		$html .= '<p><input type="submit" class="button" name="OK"  id="maj_dereferncement" value="'.$this->l('Importer les articles déréférencés').'" onclick="javascript:MAJDereferencement();" /></p>';

		$all_deref = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT `reference`, `dateDelete`
																		FROM `'._DB_PREFIX_.'ec_ecopresto_product_deleted`
																		WHERE `status`=0
																		ORDER BY `dateDelete`, `reference`');
		if (count($all_deref) != 0)
		{
			$html .= '<table class="table" id="table2">
							<thead>
								<tr>
									<th><input type="checkbox" name="Supprimer" value="'.$this->l('Supprimer').'" class="cbDerefAll" id="cbDerefAll"></th>
									<th>'.$this->l('Nom').'</th>
									<th>'.$this->l('Référence').'</th>
									<th>'.$this->l('Date').'</th>
								 </tr>
							</thead>
							<tbody>';

			foreach ($all_deref as $resu_deref)
			{
				$reference = new importerReference($resu_deref['reference']);
				$name = Db::getInstance()->getValue('SELECT `name` FROM `'._DB_PREFIX_.'product_lang` WHERE `id_product`='.(int)$reference->id_product);
				$html .= '<tr>';
				$html .= '<td><input type="checkbox" id="'.Tools::safeOutput($resu_deref['reference']).'" rel="" name="checkDeref" class="cbDeref"></td>';
				$html .= '<td>'.Tools::safeOutput($name).'</td>';
				$html .= '<td>'.Tools::safeOutput($resu_deref['reference']).'</td>';
				$html .= '<td>'.date('d/m/Y', $resu_deref['dateDelete']).'</td>';
				$html .= '</tr>';
			}
			$html .= '</tbody>';
			$html .= '</table>';
			$html .= '<p><input type="submit" class="button" name="'.$this->l('Supprimer les produits').'"  id="del_dereferncement" value="'.$this->l('Supprimer').'" onclick="javascript:DELDereferencement();" /></p>';
		}
		else
			$html .= $this->l('Aucun produit déréférencé');

		$html .= '</div>';

		$html .= '</div>';

		$html .= '<div class="footermodeco">
			<div id="infoSpe">
				<p>'.$this->l('Dernière remontée des commandes : ').Tools::safeOutput($catalog->tabConfig['DATE_ORDER']).'</p>
				<p>'.$this->l('Dernière remontée des stocks : ').Tools::safeOutput($catalog->tabConfig['DATE_STOCK']).'</p>
				<p>'.$this->l('Import catalogue Ecopresto : ').Tools::safeOutput($catalog->tabConfig['DATE_IMPORT_ECO']).'</p>
				<p>'.$this->l('Synchronisation de la sélection dans Prestashop : ').Tools::safeOutput($catalog->tabConfig['DATE_IMPORT_PS']).'</p>
				<p>'.$this->l('Mise à jour de la sélection dans le catalogue Ecopresto : ').Tools::safeOutput($catalog->tabConfig['DATE_UPDATE_SELECT_ECO']).'</p>
				<p '.(isset($tabLic[1]) && $tabLic[1] < time()?' class="aleecoef" ':'').'>'.$this->l('Date de fin d\'adhésion : ').(isset($tabLic[1])?date('d/m/Y', $tabLic[1]):'').'</p>
				<p '.(isset($tabLic[2]) && $nbTot > $tabLic[2]?' class="aleecoef" ':'').'>'.$this->l('Nombre de produits sélectionnés / Nb de produits autorisés :').$nbTot.'/'.(isset($tabLic[2])?$tabLic[2]:'').'</p>
				'.(isset($tabLic[3])?'<p '.('www.'.Configuration::get('PS_SHOP_DOMAIN') != 'www.'.$tabLic[3] && 'www.'.Configuration::get('PS_SHOP_DOMAIN') != 'http://'.$tabLic[3] && 'www.'.Configuration::get('PS_SHOP_DOMAIN') != $tabLic[3] && Configuration::get('PS_SHOP_DOMAIN') != 'www.'.$tabLic[3] && Configuration::get('PS_SHOP_DOMAIN') != $tabLic[3] && Tools::safeOutput($catalog->tabConfig['ID_ECOPRESTO']) != 'demo123456789demo123456789demo12'?' class="aleecoef" ':'').'>'.$this->l('URL du site enregistré : ').Tools::safeOutput($tabLic[3]):'').'</p>
			</div>
			 <div id="actSpeFoo">';

				if (Configuration::get('ECOPRESTO_DEMO') != 2 || $licence !== true)
				{
					$html .= '<div class="picfo">
						<img src="http://'.Tools::getShopDomainSsl().__PS_BASE_URI__.'modules/ecopresto/img/cle.png" class="actlic" />
						<h3 class="actlic">'.$this->l('Activer mon compte').'</h3>
					</div>';
				}

				$html .= '<div class="picfo">
					<a href="http://www.ecopresto.com/images/pdf/support_presta_v2_fr.pdf" target="_blank">
						<img src="http://'.Tools::getShopDomainSsl().__PS_BASE_URI__.'modules/ecopresto/img/doc.png" />
						<h3>'.$this->l('Documentation').'</h3>
					</a>
				</div>
				<div class="picfo">
					<a href="http://addons.prestashop.com/contact-community.php?id_product=11052" target="_blank">
						<img src="http://'.Tools::getShopDomainSsl().__PS_BASE_URI__.'modules/ecopresto/img/aide.png" />
						<h3>'.$this->l('Support').'</h3>
					</a>
				</div>
			</div>
			<p class="fooCop">'.$this->l('Développé par : Agence ').$this->author.' - <a href="http://www.ethercreation.com">www.ethercreation.com</a><br /><i><strong>'.$this->l('Ce module ne peut ni être diffusé, modifié, ou vendu sans l\'accord au préalable écrit de la société Ether Création').'</i></strong></p>
		</div>';

		return $html;
	}
}
