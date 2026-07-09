/*
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

import {expect, Page} from '@playwright/test';

export default class ProductPage {
    /**
     * @param {Page} page
     * @param {string} productId
     */
    async openProduct(page: Page, productId: int) {
        await page.goto(`catalog/product/view/id/${productId}`);
    }

    async addSimpleProductToCart(page: Page, quantity = 1) {
        await page.locator('[name="qty"]').clear();
        await page.locator('[name="qty"]').fill(quantity.toString());

        await page.locator('#search').focus();

        const productTitle = (await page.locator('[data-ui-id="page-title-wrapper"]').first().textContent() ?? '').trim();

        await page.locator('#product-addtocart-button').click({ force: true });
        await page.getByText(`You added ${productTitle} to your shopping cart.`).waitFor({ state: 'visible' });
        await expect(page.locator('#menu-cart-icon span').first()).toHaveText(/^[1-9][0-9]*$/);

        return productTitle;
    }
}
