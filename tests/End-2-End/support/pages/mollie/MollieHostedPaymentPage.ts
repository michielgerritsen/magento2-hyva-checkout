/*
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

export default class MollieHostedPaymentPage {
  incrementId: string;

  constructor(public expect) {
  }

  async selectStatus(page, status) {
    // Placing the order and redirecting to the hosted payment page can take a while on a
    // cold environment, so allow more time than the default action timeout.
    await this.expect(page).toHaveURL(/https:\/\/www\.mollie\.com\/checkout\//, { timeout: 30000 });

    const element = page.locator('.copyable').first();
    await element.waitFor({ state: 'visible', timeout: 30000 });
    const text = await element.getAttribute('data-clipboard-text');
    this.incrementId = text.replace('Order ', '');

    await page.click(`input[value="${status}"]`);
    await page.click('.button');
  }

  async assertIsVisible(page) {
    await this.expect(page).toHaveURL(/https:\/\/www\.mollie\.com\/checkout\//);
  }

  async selectPaymentMethod(page, method) {
    await page.locator('.payment-method-list').getByText(method).click();
  }

  async selectFirstIssuer(page) {
    await page.locator('.payment-method-list [name="issuer"]').first().click({ timeout: 30000 });
  }
}
