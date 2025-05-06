import { Controller } from '@hotwired/stimulus';

/**
 * Auth controller for handling login/signup form toggle
 *
 * @stimulus-controller auth
 */
export default class extends Controller {
    static targets = [];

    connect() {
        const container = this.element;
        const registerBtn = container.querySelector('.register-btn');
        const loginBtn = container.querySelector('.login-btn');

        registerBtn.addEventListener('click', () => {
            container.classList.add('active');
        });

        loginBtn.addEventListener('click', () => {
            container.classList.remove('active');
        });
    }
} 