function initAutocomplete() {
    const input = document.getElementById('autocomplete');
    
    if (!input) {
        return;
    }
    
    const autocomplete = new google.maps.places.Autocomplete(input, {
        types: ['geocode'],
        componentRestrictions: { country: 'au' }
    });

    autocomplete.addListener('place_changed', function() {
        const place = autocomplete.getPlace();

        if (!place.address_components) {
            return;
        }

        let suburb = '';
        let state = '';
        let postcode = '';

        for (const component of place.address_components) {
            const types = component.types;
            if (types.includes('locality')) {
                suburb = component.long_name;
            }
            if (types.includes('administrative_area_level_1')) {
                state = component.short_name;
            }
            if (types.includes('postal_code')) {
                postcode = component.long_name;
            }
        }

        // Set the values
        document.getElementById('input-payment-city').value = suburb;
        document.getElementById('input-payment-zone').value = state;
        document.getElementById('input-payment-postcode').value = postcode;
        calculateShippngCostAutomatically(postcode);

        // Now trigger to load the zone (payment zone)
        if (state !== '') {
            updatePaymentZone(state);
        }
    });
}

function updatePaymentZone(state_code) {
    // Assuming you have a select dropdown like <select name="zone_id" id="zone_id">
    // and it loads zones based on country or state.

    // Example: trigger an AJAX call here to fetch zone_id
    $.ajax({
        url: 'index.php?route=checkout/checkout/getZoneByState', // example URL (change as per your backend)
        type: 'GET',
        data: { country_id: '13', state: state_code }, // 13 = Australia (change if needed)
        dataType: 'json',
        success: function(response) {
            if (response.zone_id) {
                $('#input-payment-zone').val(response.zone_id); // set zone_id
            }
        }
    });
}

function calculateShippngCostAutomatically(postcode){
        if(postcode.length === 0) {
            $('#shipping-results').html('<p>Please enter a postcode.</p>');
            return;
        }

        // Show loader
        $('.cartNw').html(`
            <div class="loader-container">
                <div class="spinner"></div>
                <p>Loading shipping options...</p>
            </div>
        `);

        $.ajax({
            url: 'index.php?route=extension/module/product_shipping/getCost',
            type: 'post',
            data: { postcode: postcode },
            dataType: 'json',
            success: function(json) {
                if(json.success) {
                    // Save postcode in session
                    $.ajax({
                        url: 'index.php?route=extension/module/product_shipping/savePostcode',
                        type: 'post',
                        data: { postcode: postcode }
                    }).done(function() {
                        $(document).trigger('so_checkout_reload_shipping');
                        $(document).trigger('so_checkout_reload_cart');
                    });

                    
                } else {
                    $('#cartNw').html('<p>' + json.error + '</p>');
                }
            },
            error: function() {
                $('#cartNw').html('<p>An error occurred while fetching shipping options.</p>');
            }
        });
}

// Load autocomplete after window loads
window.addEventListener('load', initAutocomplete);