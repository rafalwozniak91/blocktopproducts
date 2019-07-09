<?php
/**
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
*  @author    Mirjan24 <info@mirjan24.pl>
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Blocktopproducts extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'blocktopproducts';
        $this->tab = 'administration';
        $this->version = '1.0.1';
        $this->author = 'Rafał Woźniak';
        $this->need_instance = 1;
        $this->errors = false;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Top products');
        $this->description = $this->l('Display top products in top of category');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall my module?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {

        include(dirname(__FILE__).'/sql/install.php');

        Configuration::updateValue('BLOCKTOPPRODUCTS_GENERATE', 0);
        Configuration::updateValue('BLOCKTOPPRODUCTS_WIDTH', 537);
        Configuration::updateValue('BLOCKTOPPRODUCTS_HEIGHT', 370);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('dashboardZoneOne') &&
            $this->registerHook('actionProductUpdate') &&
            $this->registerHook('topProducts');
    }

    public function uninstall()
    {

        Configuration::deleteByName('BLOCKTOPPRODUCTS_GENERATE');
        Configuration::deleteByName('BLOCKTOPPRODUCTS_WIDTH');
        Configuration::deleteByName('BLOCKTOPPRODUCTS_HEIGHT');
        
        include(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */

        if (((bool)Tools::isSubmit('submitBlocktopproductsSettings')) == true) {
            $this->postProcessSettings();
        }
        if (((bool)Tools::isSubmit('addNewProduct')) == true) {
            $this->addProducts();
        }

        if(((bool)Tools::isSubmit('deleteblocktopproducts')) == true) {
            $this->removeProducts();
        }

        $this->context->smarty->assign(
            array(
                'module_dir'=> $this->_path,
                'errors' => $this->errors
            )
        );

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderSettingsForm().$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'addNewProduct';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), 
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );


        $images_path = $_SERVER['DOCUMENT_ROOT'].$this->_path.'images/';

        $list_items = $this->getProducts();

        $fields_list = array(
            'id_blocktopproducts' => array(
                'title' => 'ID',
            ),
            'category_path' => array(
                'title' => 'Category path',
            ), 
            'id_category' => array(
                'title' => 'ID Category',
            ), 
            'id_products' => array( 
                'title' => 'ID Products',
            ),                      
        );
        $helperList = new HelperList();
        $helperList->shopLinkType = '';
        $helperList->simple_header = false;
        $helperList->actions = array('delete');
        $helperList->show_toolbar = true;
        $helperList->module = $this;
        $helperList->listTotal = count($list_items);
        $helperList->identifier = 'id_blocktopproducts';
        $helperList->title = $this->l('Products List');      
        $helperList->table = $this->name;
        $helperList->token = Tools::getAdminTokenLite('AdminModules');
        $helperList->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        return $helper->generateForm(array($this->getAddingForm())).$helperList->generateList($list_items, $fields_list);
    }

    protected function renderSettingsForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitBlocktopproductsSettings';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getSettingsFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm([$this->getSettingsForm()]);
    }

    protected function getSettingsForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Ganerate products image'),
                        'name' => 'BLOCKTOPPRODUCTS_GENERATE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this if you want to regenerate products image'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 2,
                        'type' => 'text',
                        'prefix' => 'px',
                        'desc' => $this->l('Enter a valid image width'),
                        'name' => 'BLOCKTOPPRODUCTS_WIDTH',
                        'label' => $this->l('Width'),
                    ),
                    array(
                        'col' => 2,
                        'type' => 'text',
                        'prefix' => 'px',
                        'desc' => $this->l('Enter a valid image height'),
                        'name' => 'BLOCKTOPPRODUCTS_HEIGHT',
                        'label' => $this->l('Height'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    protected function getSettingsFormValues()
    {
        return array(
                'BLOCKTOPPRODUCTS_GENERATE' => Configuration::get('BLOCKTOPPRODUCTS_GENERATE'),
                'BLOCKTOPPRODUCTS_WIDTH' => Configuration::get('BLOCKTOPPRODUCTS_WIDTH'),
                'BLOCKTOPPRODUCTS_HEIGHT' => Configuration::get('BLOCKTOPPRODUCTS_HEIGHT'),
            );

    }

    protected function getAddingForm()
    {

        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Adding new product'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 1,
                        'type' => 'text',
                        'id' => 'id_product', 
                        'desc' => $this->l('Enter a id of product'),
                        'name' => 'BLOCKTOPPRODUCTS_PRODUCT_ID',
                        'label' => $this->l('ID product'),
                    ), 
                     array(
                    'type'  => 'categories',
                    'label' => $this->l('Select categories to display product'),
                    'name'  => 'BLOCKTOPPRODUCTS_ID_CATEGORIES',
                    'tree'  => array(
                        'id' => 'categories-tree',
                        'use_checkbox' => true,
                        'disabled_categories' => false,
                        'root_category' => (int)Configuration::get('PS_HOME_CATEGORY'),
                        'full_tree' => true,
                        'use_search' => true, 
                        )
                    ),
                ),

                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    protected function getConfigFormValues()
    {
        return array(
            'BLOCKTOPPRODUCTS_PRODUCT_ID' => Configuration::get('BLOCKTOPPRODUCTS_PRODUCT_ID'),
            'BLOCKTOPPRODUCTS_ID_CATEGORY' => Configuration::get('BLOCKTOPPRODUCTS_ID_CATEGORY'),
        );
    }


    protected function createCategoryPath($id_category, $id_lang) {

        $category = new Category($id_category);

        $parents = $category->getParentsCategories($id_lang);

        foreach ($parents as $parent) {
            $path[] = $parent['name'];
        }

        return implode(' > ', array_reverse($path));
    }

    /** 
     * Save form data.
     */
    protected function addProducts()
    {

        $id_product = Tools::getValue('BLOCKTOPPRODUCTS_PRODUCT_ID');
        $id_categories = str_replace(' ', '', Tools::getValue('BLOCKTOPPRODUCTS_ID_CATEGORIES'));
        $width = (int)Tools::getValue('BLOCKTOPPRODUCTS_WIDTH');
        $height = (int)Tools::getValue('BLOCKTOPPRODUCTS_HEIGHT');

        $this->copyImage((int)$id_product, $width, $height);

        if(is_numeric($id_product) && !empty($id_categories)) {
            foreach ($id_categories as $id_category) {
                
                if(!$this->recordExist($id_category, $id_product)) {
                    Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'blocktopproducts` (`id_products`, `id_category`) VALUES ("'.(int)$id_product.'", '.(int)$id_category.')');
                } else {
                    $this->errors = Tools::displayError('This product already exists');
                }
            } 
        } else {
            $this->errors = Tools::displayError('Id product must be a number format');
        }

    }

    protected function removeProducts() {

        return Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'blocktopproducts` WHERE id_blocktopproducts = '.Tools::getValue('id_blocktopproducts')); 
    }

    protected function postProcessSettings()
    {

        $width = (int)Tools::getValue('BLOCKTOPPRODUCTS_WIDTH');
        $height = (int)Tools::getValue('BLOCKTOPPRODUCTS_HEIGHT');

        Configuration::updateValue('BLOCKTOPPRODUCTS_WIDTH', $width);
        Configuration::updateValue('BLOCKTOPPRODUCTS_HEIGHT', $height);

        if(Tools::isSubmit('BLOCKTOPPRODUCTS_GENERATE')) {
            $this->regenerateImages($width, $height);
        }   

    }

    public function regenerateImages($img_width, $img_height) {

        $products = $this->getProducts();

        foreach($products as $product) {
            $this->copyImage((int)$product['id_products'], $img_width, $img_height);
        }

    }

    protected function getProducts() {


        $topproducts = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'blocktopproducts`');


        foreach ($topproducts as &$item) {

            $item['category_path'] = $this->createCategoryPath($item['id_category'], $this->context->language->id);
    
        }

        return $topproducts;
    }

    protected function recordExist($id_category, $id_product) {

        $result = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'blocktopproducts` WHERE id_category ='.(int)$id_category.' AND id_products ='.(int)$id_product);

        return empty($result) ? false : true;

    }

    public function checkProductExist($id_product) {

        $result = Db::getInstance()->getValue('SELECT id_products FROM `'._DB_PREFIX_.'blocktopproducts` WHERE id_products ='.(int)$id_product);

        return empty($result) ? false : true;

    }
    
    public function copyImage($id_product, $w, $h) {

        $id_lang = $this->context->language->id;
        $id_shop = $this->context->shop->id;

        $cover = Product::getCover($id_product);
        $product = new Product((int)$id_product, false, $id_lang, $id_shop);
        $id_default_attribute = Product::getDefaultAttribute((int)$id_product, 0, true);
        $images = Image::getImages($id_lang, $id_product, $id_default_attribute);

        $id_image = !empty($images[0]['id_image']) ? $images[0]['id_image'] : $cover['id_image'];

        $image = new Image((int)$id_image);
        $base_img_path = _PS_PROD_IMG_DIR_.$image->getExistingImgPath().'.'.$image->image_format;
        $current_img_path = _PS_MODULE_DIR_.$this->name.'/images/'.$id_product.'.'.$image->image_format;

        if (file_exists($base_img_path)) { 
            list($width, $height) = getimagesize($base_img_path);

            if($width != $height) {
                $ratio = $width/$height;

                $new_width = $w;
                $new_height = $w/$ratio;
            } else {
                $new_width = $w;
                $new_height = $h;
            }
    
            return ImageManager::resize($base_img_path, $current_img_path, (int)$new_width, (int)$new_height, $image->image_format);

        } else {
            return false;
        }
    
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/blocktopproducts.css');
    }

    public function hookTopProducts($params)
    {

        $ids_products = Db::getInstance()->executeS('SELECT id_products FROM `'._DB_PREFIX_.'blocktopproducts` WHERE id_category ='.(int)$params['id_category']);

        if(count($ids_products) > 0) {
            foreach ($ids_products as $products) {

            $id_products = explode(',', $products['id_products']);

                foreach ($id_products as $key => $id_product) {

                    $product = new Product($id_product, true, $this->context->language->id, $this->context->shop->id);
                    if($product->id && $product->active) 
                        $topproducts[] = array(
                            'id_product' => $product->id_product,
                            'name' => $product->name,
                            'price' => $product->getPriceStatic($product->id_product, true),
                            'description_short' => $product->description_short,
                            'delivery_time' => $product->supplier_name,
                            'on_sale' => $product->on_sale,
                            'specific_prices' => $product->specificPrice,
                            'wholesale_price' => $product->wholesale_price,
                            'price_without_reduction' => Product::getPriceStatic(
                                (int)$product->id_product,
                                true,
                                (int)$product->getDefaultAttribute($product->id_product),
                                2,
                                null,
                                false,
                                false
                            ),
                            'image' => '',
                            'link' => $product->getLink(),
                        );
                }

            }
        } else {
            $topproducts = false;
        }

        $this->context->smarty->assign('topproducts', (count($topproducts) > 2 ? array_slice($topproducts, 0, 2) : $topproducts));

        return $this->context->smarty->fetch($this->local_path.'views/templates/hook/blocktopproducts.tpl');

    }


    public function getCoutProductsByIdCategory($id_category)
    {

    	return Db::getInstance()->getValue('SELECT COUNT(id_products) FROM `'._DB_PREFIX_.'blocktopproducts` tp
                                            LEFT JOIN `'._DB_PREFIX_.'product` p ON (tp.id_products = p.id_product)
                                            WHERE tp.id_category = '.(int)$id_category.' AND p.active = 1');
    }

    public function getTopCategory() 
    {

        $topProductsCategories = Db::getInstance()->executeS('SELECT DISTINCT id_category FROM `'._DB_PREFIX_.'blocktopproducts`');

        $output = [];

        foreach ($topProductsCategories as &$item) {

        	$products_count = (int)$this->getCoutProductsByIdCategory($item['id_category']);

        	if($products_count < 2) {

        		$output[] = [
        			'id_category' => $item['id_category'],
        			'category_path' => $this->createCategoryPath($item['id_category'], $this->context->language->id),
        			'products_count' => $products_count
        		];
        	}
   
        }

        return $output;
    }

    public function hookDashboardZoneOne()
    {
       
    	$topProductsCategories = $this->getTopCategory();

    	$this->context->smarty->assign([
    		'topProductsCategories' => $topProductsCategories,
    	]);

    	

    	return $this->context->smarty->fetch($this->local_path.'views/templates/hook/dashboard.tpl');

    }

    public function hookActionProductUpdate($params)
    {
        $id_product = (int)Tools::getValue('id_product');

        if($this->checkProductExist($id_product)) {
            $width = (int)Tools::getValue('BLOCKTOPPRODUCTS_WIDTH');
            $height = (int)Tools::getValue('BLOCKTOPPRODUCTS_HEIGHT');
    
            $this->copyImage((int)$id_product, $width, $height); 
        }

    }

}
