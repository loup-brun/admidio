/**
 * Add Stripe.js frontend code
 * 
 * @see https://stripe.com/docs/elements#setup
 */

window.onload = function () {

// Step 1: Set up Stripe Elements
//test key: pk_test_QfKSmWhXOWW9ZCkGdKtkL9p6
var stripe = Stripe('pk_test_QfKSmWhXOWW9ZCkGdKtkL9p6');
//live key: pk_live_ccR6ujsv04d7TjBjRvVBgFvn
//var stripe = Stripe('pk_live_ccR6ujsv04d7TjBjRvVBgFvn');
var elements = stripe.elements();

// Step 2: Create your payment form
// Custom styling can be passed to options when creating an Element.
var style = {
  base: {
    // Add your base input styles here. For example:
    fontSize: '16px',
    lineHeight: '24px'
  }
};

// Create an instance of the card Element
var card = elements.create('card', {style: style});

// Add an instance of the card Element into the `card-element` <div>
card.mount('#card-element');

// Card validation
card.addEventListener('change', function(event) {
  var displayError = document.getElementById('card-errors');
  if (event.error) {
    displayError.textContent = event.error.message;
  } else {
    displayError.textContent = '';
  }
});

// Step 3: Create a token to securely transmit card information
// Create a token or display an error when the form is submitted.
var form = document.getElementById('payment-form');
form.addEventListener('submit', function(event) {
  event.preventDefault();

  stripe.createToken(card).then(function(result) {
    if (result.error) {
      // Inform the user if there was an error
      var errorElement = document.getElementById('card-errors');
      errorElement.textContent = result.error.message;
    } else {
      // Send the token to your server
      stripeTokenHandler(result.token);
    }
  });
});

// Step 4: Submit the token and the rest of your form to your server
function stripeTokenHandler(token) {
  // Insert the token ID into the form so it gets submitted to the server
  var form = document.getElementById('payment-form');
  var hiddenInput = document.createElement('input');
  hiddenInput.setAttribute('type', 'hidden');
  hiddenInput.setAttribute('name', 'stripeToken');
  hiddenInput.setAttribute('value', token.id);
  form.appendChild(hiddenInput);

  // Submit the form
  form.submit();
}
};