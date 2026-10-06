// MEC Google Places integration.
(function (window) {
    window.mecPlaceAutocompleteSync = function (form) {
        if (!form) return;

        Array.prototype.forEach.call(form.querySelectorAll('input[data-mec-place-autocomplete]'), function (input) {
            var autocomplete = input.mecPlaceAutocomplete;
            if (autocomplete) input.value = autocomplete.value || input.value;
        });
    };

    window.mecPlaceAutocomplete = function (input, options) {
        if (!input || input.dataset.mecPlaceAutocomplete || !window.google || !google.maps.importLibrary) return;

        input.dataset.mecPlaceAutocomplete = '1';
        options = options || {};

        google.maps.importLibrary('places').then(function (library) {
            if (!library.PlaceAutocompleteElement) return;

            var inputId = input.id;
            var autocomplete = new library.PlaceAutocompleteElement({
                placeholder: input.placeholder,
                value: input.value
            });

            autocomplete.id = inputId;
            autocomplete.className = input.className;
            autocomplete.style.display = 'block';
            autocomplete.style.width = '100%';

            input.id = inputId + '_value';
            input.type = 'hidden';
            input.mecPlaceAutocomplete = autocomplete;
            input.parentNode.insertBefore(autocomplete, input);

            var syncValue = function () {
                input.value = autocomplete.value || input.value;
            };

            autocomplete.addEventListener('input', syncValue);
            autocomplete.addEventListener('change', syncValue);
            if (input.form) input.form.addEventListener('submit', syncValue);
            autocomplete.addEventListener('gmp-select', function (event) {
                var place = event.placePrediction.toPlace();
                place.fetchFields({fields: ['formattedAddress', 'location']}).then(function () {
                    input.value = place.formattedAddress || autocomplete.value || '';
                    if (options.latitude && place.location) options.latitude.value = place.location.lat();
                    if (options.longitude && place.location) options.longitude.value = place.location.lng();
                });
            });
        }).catch(function () {
            delete input.dataset.mecPlaceAutocomplete;
        });
    };
}(window));
