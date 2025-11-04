import './bootstrap';


/*
 * This file would typically import Alpine.js, Vue, React, or custom JavaScript
 * needed for the admin panel Blade views.
 * It gets compiled into public/build/assets/ by Vite.
 */

// Example: Import Bootstrap's JavaScript (if using Bootstrap)
// import * as bootstrap from 'bootstrap';

// Example: Import Alpine.js (if using Alpine)
// import Alpine from 'alpinejs';
// window.Alpine = Alpine;
// Alpine.start();

console.log('Admin panel JavaScript loaded.');

// Example: Add confirmation for delete buttons
document.addEventListener('DOMContentLoaded', () => {
  const deleteForms = document.querySelectorAll('form[method="POST"][onsubmit*="confirm"]');
  deleteForms.forEach(form => {
    // Note: Inline onsubmit is already handling confirm, this is just an alternative/example
    // form.addEventListener('submit', function (event) {
    //   if (!confirm('ñ{ìñÇ…çÌèúÇµÇ‹Ç∑Ç©ÅH')) {
    //     event.preventDefault();
    //   }
    // });
    // Ensure the hidden _method field is present for DELETE
     if (!form.querySelector('input[name="_method"][value="DELETE"]')) {
         const methodInput = document.createElement('input');
         methodInput.setAttribute('type', 'hidden');
         methodInput.setAttribute('name', '_method');
         methodInput.setAttribute('value', 'DELETE');
         form.appendChild(methodInput);
     }
      // Ensure CSRF token is present
     if (!form.querySelector('input[name="_token"]')) {
         const csrfInput = document.createElement('input');
         csrfInput.setAttribute('type', 'hidden');
         csrfInput.setAttribute('name', '_token');
         // This assumes the CSRF token is available globally (e.g., via a meta tag)
         // You might need a more robust way to get the token in a real app
         const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
         if(csrfToken) {
            csrfInput.setAttribute('value', csrfToken);
            form.appendChild(csrfInput);
         } else {
            console.warn('CSRF token not found for delete form.');
         }
     }

  });
});
