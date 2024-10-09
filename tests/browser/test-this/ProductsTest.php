<?php

/**
 * ProductsTest
 */
class ProductsTest extends BrowserTestCase
{
    use \October\Test\Tests\Browser\Concerns\InteractsWithAuth;

    /**
     * testBrowserLogin
     */
    public function testBrowserLogin()
    {
        $this->browse(function($browser) {
            $this->loginToBrowser($browser);
        });
    }

    /**
     * testProductsIndex
     */
    public function testProductsIndex()
    {
        $this->browse(function($browser) {
            $browser
                ->visit('/admin/october/test/products?_site_id=1')
                ->assertTitleContains('Manage Products |')
                ->waitForTextIn('#Lists tr:nth-child(1) .list-cell-index-8', 'Tezla')
                ->waitForTextIn('#Lists tr:nth-child(2) .list-cell-index-8', 'Neztle')
            ;

            $browser
                ->click('.list-cell-index-1')
                ->waitForLocation('/admin/october/test/products/update/1')
                ->waitForEvent('page:load', 'document')
                ->assertTitleContains('Edit Product |');

            // Pre-populate repeater
            $browser
                ->click('a[href="#primarytab-bulk-pricing"]')
                ->pause(300)
                ->waitUntilMissing('[data-request="formPrices::onAddItem"]')
                ->check('#Form-field-Product-bulk_pricing')
                ->waitFor('[data-request="formPrices::onAddItem"]')
                ->click('[data-request="formPrices::onAddItem"]')
                ->waitFor('#Form-formPricesForm1-field-Product-prices-1-bulk_price')
                ->click('[data-request="formPrices::onAddItem"]')
                ->waitFor('#Form-formPricesForm2-field-Product-prices-2-bulk_price')
            ;

            // Un-populate repeater
            $browser
                ->pause(300)
                ->uncheck('#Form-field-Product-bulk_pricing')
                ->waitUntilMissing('#Form-formPricesForm1-field-Product-prices-1-bulk_price')
                ->waitUntilMissing('#Form-formPricesForm2-field-Product-prices-2-bulk_price')
            ;

            // Test "between" validation of relation
            $browser
                ->click('a[href="#primarytab-location"]')
                ->pause(300)
                ->check('#checkbox_Form-field-Product-location_sold_1')
                ->check('#checkbox_Form-field-Product-location_sold_2')
                ->check('#checkbox_Form-field-Product-location_sold_3')
                ->click('.form-buttons [data-request=onSave]')
                ->waitForTextIn('.oc-flash-message.error', 'The Locations Sold field must be between')
                ->click('a.flash-close')
            ;

            $browser
                ->uncheck('#checkbox_Form-field-Product-location_sold_3')
                ->click('.form-buttons [data-request=onSave]')
                ->waitForTextIn('.oc-flash-message.success', 'Product Updated')
            ;

            // Finish up
            $browser
                ->pause(300)
                ->click('.form-buttons [data-request=onSave]')
                ->waitForTextIn('.oc-flash-message.success', 'Product Updated')
                ->click('a.flash-close')
            ;
        });
    }

    /**
     * testProductCategoriesIndex
     */
    public function testProductCategoriesIndex()
    {
        $this->browse(function($browser) {
            $browser
                ->visit('/admin/october/test/productcategories')
                ->assertTitleContains('Manage Product Categories |');

            // Reset List
            $browser
                ->click('#Toolbar-listToolbar .toolbar-setup > a')
                ->waitForTextIn('.modal-title', 'List Setup')
                ->pause(300)
                ->press('Reset to Default')
                ->waitUntilMissing('[data-request="list::onResetSetup"]')
            ;

            // Expanding and collapsing tree
            $browser
                ->click('[data-tree-id="1"] a.tree-expand-collapse.is-expanded')
                ->waitUntilMissingText("And I'm not proud of my address")
            ;

            $browser
                ->click('[data-tree-id="1"] a.tree-expand-collapse')
                ->waitForText("And I'm not proud of my address")
            ;

            // Sorting and paging list
            $browser
                ->click('#ListStructure .list-cell-name-name a')
                ->waitUntilMissing('html[data-ajax-progress]')
                ->pause(300)
                ->waitFor('#ListStructure .list-cell-name-name.active a')
            ;

            $browser
                ->waitForTextIn('#ListStructure .list-cell-index-1', 'Vegetables')
                ->click('#ListStructure .list-pagination-links .page-link.page-next')
                ->waitForTextIn('#ListStructure .list-cell-index-1', 'Perfume')
                ->click('#ListStructure .list-pagination-links .page-link.page-back')
                ->waitForTextIn('#ListStructure .list-cell-index-1', 'Vegetables')
                ->click('#ListStructure .list-cell-name-name a')
                ->waitForTextIn('#ListStructure .list-cell-index-1', 'Automotive')
                ->click('#ListStructure .list-cell-name-name a')
                ->waitUntilMissing('#ListStructure .list-pagination')
            ;

            // Searching and paging list
            $browser
                ->type('listToolbarSearch[term]', 'a ')
                ->waitFor('#ListStructure .list-pagination-links')
                ->waitForTextIn('#ListStructure .list-cell-index-1', 'Mains')
                ->click('#ListStructure .list-pagination-links .page-link.page-next')
                ->waitForTextIn('#ListStructure .list-cell-index-1', 'Hats')
                ->click('#Search-listToolbarSearch .clear-input-text')
                ->waitUntilMissing('#ListStructure .list-pagination')
            ;

            // Update record
            $browser
                ->click('.list-cell-index-1')
                ->waitForLocation('/admin/october/test/productcategories/update/1')
                ->waitForEvent('page:load', 'document')
                ->assertTitleContains('Edit Product Category |');

            $browser
                ->pause(300)
                ->scrollIntoView('.form-buttons')
                ->pause(300)
                ->click('.form-buttons [data-request=onSave]')
                ->waitForTextIn('.oc-flash-message.success', 'Product Category Updated')
                ->click('a.flash-close');
        });
    }

    /**
     * testProductCategoriesCreate
     */
    public function testProductCategoriesCreate()
    {
        $this->browse(function($browser) {
            $browser
                ->visit('/admin/october/test/productcategories')
                ->assertTitleContains('Manage Product Categories |')
            ;

            $browser
                ->clickLink('New Product Category')
                ->waitForLocation('/admin/october/test/productcategories/create')
                ->waitForEvent('page:load', 'document')
            ;

            $browser
                ->type('ProductCategory[name]', 'Dusk Test')
                ->click('.form-buttons [data-request=onSave]')
                ->waitForTextIn('.oc-flash-message.success', 'Product Category Created')
                ->click('a.flash-close')
            ;

            $browser
                ->ajaxRequest('.form-buttons [data-request=onDelete]')
                ->waitForTextIn('.modal-body > p', 'Delete record?')
                ->press('OK')
            ;

            $browser
                ->waitForLocation('/admin/october/test/productcategories')
                ->waitForTextIn('.oc-flash-message.success', 'Product Category Deleted')
                ->click('a.flash-close')
            ;
        });
    }
}
