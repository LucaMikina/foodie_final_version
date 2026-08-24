
(function ($) {
  'use strict';

  const CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content') || '';
  const BASE = (window.APP_BASE_URL || '') + '/public/index.php';

  $(document).on('click', '.js-dodaj-u-kosaricu', function (e) {
    e.preventDefault();
    const $btn = $(this);
    const jeloId = $btn.data('jelo-id');
    const kolicina = $btn.closest('.jelo-kartica').find('.js-kolicina').val() || 1;

    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" aria-hidden="true"></span><span>Dodajem...</span>');

    $.post(BASE + '?akcija=kosarica_dodaj', {
      jelo_id: jeloId,
      kolicina: kolicina,
      csrf_token: CSRF_TOKEN,
    })
      .done(function (odgovor) {
        if (odgovor && odgovor.uspjeh) {
          osvjeziBrojacKosarice(odgovor.kosarica);
          prikaziToast('Dodano u košaricu.');
        } else {
          prikaziToast(odgovor.greska || 'Greška prilikom dodavanja.', true);
        }
      })
      .fail(function (xhr) {
        const poruka = xhr && xhr.responseJSON && xhr.responseJSON.greska
          ? xhr.responseJSON.greska
          : 'Greška u komunikaciji sa serverom.';
        prikaziToast(poruka, true);
      })
      .always(function () {
        $btn.prop('disabled', false).html('<i class="bi bi-plus-lg"></i><span>Dodaj</span>');
      });
  });

  function osvjeziBrojacKosarice(stavke) {
    const ukupno = (stavke || []).reduce((zbroj, s) => zbroj + parseInt(s.kolicina, 10), 0);
    $('.js-kosarica-brojac').text(ukupno).toggleClass('d-none', ukupno === 0);
  }

  if ($('.js-kosarica-brojac').length) {
    $.getJSON(BASE + '?akcija=kosarica_prikazi')
      .done(function (stavke) { osvjeziBrojacKosarice(stavke); });
  }

  function checkoutMoney(vrijednost) {
    const broj = Number(vrijednost || 0);
    return broj.toLocaleString('hr-HR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' KM';
  }

  function osvjeziCheckoutSaKosaricom(stavke) {
    const lista = Array.isArray(stavke) ? stavke : [];
    const ukupnoKomada = lista.reduce((sum, s) => sum + parseInt(s.kolicina || 0, 10), 0);
    const ukupnaCijena = lista.reduce((sum, s) => sum + (Number(s.cijena || 0) * parseInt(s.kolicina || 0, 10)), 0);

    osvjeziBrojacKosarice(lista);
    $('.js-checkout-item-count').text(ukupnoKomada + (ukupnoKomada === 1 ? ' stavka' : ' stavki'));
    $('.js-checkout-subtotal').text(checkoutMoney(ukupnaCijena));
    $('.js-checkout-total[data-subtotal]').attr('data-subtotal', ukupnaCijena.toFixed(2));
    if (typeof refreshCheckoutTotal === 'function') {
      refreshCheckoutTotal();
    } else {
      $('.js-checkout-total').text(checkoutMoney(ukupnaCijena));
    }

    lista.forEach(function (stavka) {
      const $red = $('.js-cart-row[data-cart-id="' + stavka.id + '"]');
      if (!$red.length) return;
      const kolicina = parseInt(stavka.kolicina || 1, 10);
      const cijena = Number(stavka.cijena || $red.data('unit-price') || 0);
      $red.find('.js-cart-qty-value').text(kolicina);
      $red.find('.js-cart-line-total').text(checkoutMoney(cijena * kolicina));
    });
  }

  $(document).on('click', '.js-cart-qty', function () {
    const $btn = $(this);
    const $red = $btn.closest('.js-cart-row');
    if (!$red.length) return;

    const cartId = parseInt($red.attr('data-cart-id'), 10);
    const trenutna = parseInt($red.find('.js-cart-qty-value').text(), 10) || 1;
    const smjer = $btn.attr('data-direction');
    const nova = smjer === 'plus' ? Math.min(99, trenutna + 1) : Math.max(1, trenutna - 1);
    if (nova === trenutna) return;

    $red.addClass('is-updating');
    $.post(BASE + '?akcija=kosarica_azuriraj', {
      cart_id: cartId,
      kolicina: nova,
      csrf_token: CSRF_TOKEN,
    })
      .done(function (odgovor) {
        if (odgovor && odgovor.uspjeh) {
          osvjeziCheckoutSaKosaricom(odgovor.kosarica);
        } else {
          prikaziToast((odgovor && odgovor.greska) || 'Količinu nije moguće promijeniti.', true);
        }
      })
      .fail(function (xhr) {
        prikaziToast((xhr.responseJSON && xhr.responseJSON.greska) || 'Greška pri ažuriranju košarice.', true);
      })
      .always(function () {
        $red.removeClass('is-updating');
      });
  });

  $(document).on('click', '.js-cart-remove', function () {
    const $btn = $(this);
    const $red = $btn.closest('.js-cart-row');
    if (!$red.length) return;
    const cartId = parseInt($red.attr('data-cart-id'), 10);

    $red.addClass('is-updating');
    $.post(BASE + '?akcija=kosarica_ukloni', {
      cart_id: cartId,
      csrf_token: CSRF_TOKEN,
    })
      .done(function (odgovor) {
        if (!(odgovor && odgovor.uspjeh)) {
          prikaziToast((odgovor && odgovor.greska) || 'Stavku nije moguće ukloniti.', true);
          $red.removeClass('is-updating');
          return;
        }

        $red.removeClass('is-updating').addClass('is-removing');
        window.setTimeout(function () {
          $red.remove();
          osvjeziCheckoutSaKosaricom(odgovor.kosarica);
          if (!odgovor.kosarica || odgovor.kosarica.length === 0) {
            window.location.reload();
          }
        }, 180);
      })
      .fail(function (xhr) {
        $red.removeClass('is-updating');
        prikaziToast((xhr.responseJSON && xhr.responseJSON.greska) || 'Greška pri uklanjanju stavke.', true);
      });
  });

  function prikaziToast(poruka, jeGreska) {
    const $kontejner = $('#foodie-toasts');
    if ($kontejner.length === 0) return;

    const ikona = jeGreska ? 'bi-exclamation-circle-fill' : 'bi-check-lg';
    const klasa = jeGreska ? 'foodie-toast-error' : 'foodie-toast-success';
    const $toast = $(
      '<div class="toast foodie-toast ' + klasa + ' align-items-center" role="alert" aria-live="assertive" aria-atomic="true">' +
      '  <div class="d-flex align-items-center">' +
      '    <div class="toast-body"><i class="bi ' + ikona + '"></i><span>' + poruka + '</span></div>' +
      '    <button type="button" class="btn-close me-3" data-bs-dismiss="toast" aria-label="Zatvori"></button>' +
      '  </div>' +
      '</div>'
    );
    $kontejner.append($toast);
    const toast = new bootstrap.Toast($toast[0], { delay: 3000 });
    toast.show();
    $toast.on('hidden.bs.toast', () => $toast.remove());
  }

  const dishModalElement = document.getElementById('dishDetailModal');
  const dishModal = dishModalElement && window.bootstrap ? new bootstrap.Modal(dishModalElement) : null;

  function otvoriDetaljJela($kartica) {
    if (!dishModal || !$kartica.length) return;

    const naziv = String($kartica.data('jelo-naziv') || 'Jelo');
    const opis = String($kartica.data('jelo-opis') || '');
    const cijena = Number($kartica.data('jelo-cijena') || 0);
    const slika = String($kartica.data('jelo-slika') || '');
    const jeloId = parseInt($kartica.data('jelo-id'), 10) || 0;

    $('#dishDetailTitle').text(naziv);
    $('#dishDetailDescription').text(opis || 'Ukusno jelo iz odabranog restorana.');
    $('#dishDetailPrice').text(checkoutMoney(cijena));
    $('#dishDetailImage').attr({ src: slika, alt: naziv });
    $('#dishDetailQty').val(1);
    $('#dishDetailAdd').attr('data-jelo-id', jeloId).data('jelo-id', jeloId);

    dishModal.show();
  }

  $(document).on('click', '.js-dish-open', function (e) {
    if ($(e.target).closest('button, input, a, label').length) return;
    otvoriDetaljJela($(this));
  });

  $(document).on('keydown', '.js-dish-open', function (e) {
    if (e.key !== 'Enter' && e.key !== ' ') return;
    if ($(e.target).is('button, input, a, label')) return;
    e.preventDefault();
    otvoriDetaljJela($(this));
  });

  $(document).on('click', '.js-modal-qty-minus, .js-modal-qty-plus', function () {
    const $input = $('#dishDetailQty');
    const trenutna = parseInt($input.val(), 10) || 1;
    const nova = $(this).hasClass('js-modal-qty-plus') ? Math.min(99, trenutna + 1) : Math.max(1, trenutna - 1);
    $input.val(nova);
  });

  $(document).on('click', '#dishDetailAdd', function () {
    if (!dishModalElement) return;
    window.setTimeout(function () {
      const btn = document.getElementById('dishDetailAdd');
      if (btn && !btn.disabled && dishModal) dishModal.hide();
    }, 550);
  });

  const $checkoutAddress = $('#adresa_dostave');
  const $checkoutAddressHidden = $('#checkout_address_value');
  const $checkoutForm = $('#checkout-order-form');
  const $deliveryFee = $('.js-delivery-fee');
  const $deliveryDistance = $('.js-delivery-distance');
  const $deliveryFeePreview = $('#delivery_fee_preview');
  const $deliveryKmPreview = $('#delivery_km_preview');
  let deliveryQuotePending = false;

  function formatMoney(value) {
    return Number(value || 0).toFixed(2).replace('.', ',') + ' KM';
  }

  function checkoutSubtotal() {
    const raw = $('.js-checkout-total[data-subtotal]').first().attr('data-subtotal');
    return Number(raw || 0);
  }

  function refreshCheckoutTotal() {
    const total = checkoutSubtotal() + Number($deliveryFeePreview.val() || 0);
    $('.js-checkout-total').text(formatMoney(total));
  }

  function resetDeliveryQuote() {
    $deliveryFeePreview.val('0');
    $deliveryKmPreview.val('0');
    $deliveryFee.text('Izračun...');
    $deliveryDistance.text('');
    refreshCheckoutTotal();
  }

  function calculateDeliveryQuote(lat, lng) {
    if (!$checkoutForm.length) return;
    deliveryQuotePending = true;
    resetDeliveryQuote();

    $.getJSON(BASE + '?akcija=dostava_izracunaj', { lat: lat, lng: lng })
      .done(function (response) {
        const delivery = response && response.dostava ? response.dostava : null;
        if (!delivery) {
          throw new Error('Nedostaje izračun dostave.');
        }
        const fee = Number(delivery.cijena || 0);
        const km = Number(delivery.km || 0);
        $deliveryFeePreview.val(fee.toFixed(2));
        $deliveryKmPreview.val(km.toFixed(2));
        $deliveryFee.text(formatMoney(fee));
        $deliveryDistance.text('(' + km.toFixed(1).replace('.', ',') + ' km, oko ' + Number(delivery.trajanje_min || 0) + ' min)');
        refreshCheckoutTotal();
      })
      .fail(function (xhr) {
        $deliveryFeePreview.val('0');
        $deliveryKmPreview.val('0');
        $deliveryFee.text('Nije dostupno');
        $deliveryDistance.text('');
        refreshCheckoutTotal();
        const message = xhr && xhr.responseJSON && xhr.responseJSON.greska
          ? xhr.responseJSON.greska
          : 'Nije moguće izračunati cijenu dostave.';
        prikaziToast(message, true);
      })
      .always(function () {
        deliveryQuotePending = false;
      });
  }

  if ($checkoutAddress.length && $checkoutAddressHidden.length) {
    function syncCheckoutAddress() {
      $checkoutAddressHidden.val(($checkoutAddress.val() || '').trim());
    }
    $checkoutAddress.on('input change', syncCheckoutAddress);
    syncCheckoutAddress();
  }

  $('.checkout-delivery-option').on('click', function (e) {
    if ($(e.target).is('input')) return;
    $(this).find('.js-delivery-time-mode').prop('checked', true).trigger('change');
  });

  $(document).on('change', '.js-delivery-time-mode', function () {
    const zakazano = $('.js-delivery-time-mode:checked').val() === 'scheduled';
    $('#scheduled-delivery-wrap').toggleClass('d-none', !zakazano);
    if (!zakazano) $('#zeljeno_vrijeme_dostave').val('');
  });

  if ($checkoutForm.length) {
    $checkoutForm.on('submit', function (e) {
      if ($checkoutAddressHidden.length) {
        const adresa = ($checkoutAddress.val() || '').trim();
        $checkoutAddressHidden.val(adresa);
        if (!adresa) {
          e.preventDefault();
          prikaziToast('Upišite adresu dostave.', true);
          $checkoutAddress.trigger('focus');
          return;
        }
      }

      if (!$('#delivery_lat').val() || !$('#delivery_lng').val()) {
        e.preventDefault();
        prikaziToast('Odaberite lokaciju dostave na karti.', true);
        return;
      }

      if (deliveryQuotePending || Number($deliveryFeePreview.val() || 0) <= 0) {
        e.preventDefault();
        prikaziToast('Pričekajte da se izračuna cijena dostave.', true);
        return;
      }

      if ($('.js-delivery-time-mode:checked').val() === 'scheduled' && !$('#zeljeno_vrijeme_dostave').val()) {
        e.preventDefault();
        prikaziToast('Odaberite datum zakazane dostave.', true);
        $('#zeljeno_vrijeme_dostave').trigger('focus');
        return;
      }

      $('#checkout-submit').prop('disabled', true).find('span:first').text('Šaljem narudžbu...');
    });
  }

  const deliveryMapElement = document.getElementById('delivery-map');
  if (deliveryMapElement && window.L) {
    const defaultLat = 43.3438;
    const defaultLng = 17.8078;
    const map = L.map(deliveryMapElement, {
      zoomControl: true,
      scrollWheelZoom: false,
    }).setView([defaultLat, defaultLng], 13);

    L.tileLayer('https://maps.geoapify.com/v1/tile/positron/{z}/{x}/{y}.png?apiKey=' + encodeURIComponent(window.GEOAPIFY_API_KEY || ''), {
      maxZoom: 20,
      attribution: '&copy; OpenStreetMap contributors &copy; Geoapify',
    }).addTo(map);

    let marker = null;
    const $lat = $('#delivery_lat');
    const $lng = $('#delivery_lng');
    const $status = $('#delivery-map-status');
    const $coords = $('#delivery-map-coordinates');

    function postaviLokaciju(lat, lng, centerMap) {
      const safeLat = Math.max(-90, Math.min(90, Number(lat)));
      const safeLng = Math.max(-180, Math.min(180, Number(lng)));
      if (!Number.isFinite(safeLat) || !Number.isFinite(safeLng)) return;

      if (!marker) {
        marker = L.marker([safeLat, safeLng], { draggable: true }).addTo(map);
        marker.on('dragend', function () {
          const p = marker.getLatLng();
          postaviLokaciju(p.lat, p.lng, false);
        });
      } else {
        marker.setLatLng([safeLat, safeLng]);
      }

      $lat.val(safeLat.toFixed(7));
      $lng.val(safeLng.toFixed(7));
      $status.html('<i class="bi bi-check-circle-fill"></i> Lokacija dostave odabrana');
      $coords.text(safeLat.toFixed(5) + ', ' + safeLng.toFixed(5));
      if (centerMap) map.setView([safeLat, safeLng], 16);
      calculateDeliveryQuote(safeLat, safeLng);
    }

    const $addressSearchButton = $('#search-delivery-address');
    const $addressResults = $('#delivery-search-results');
    const $addressHint = $('#delivery-search-hint');

    function sakrijRezultateAdrese() {
      $addressResults.empty().addClass('d-none');
    }

    function pretraziAdresu() {
      const query = ($checkoutAddress.val() || '').trim();
      if (query.length < 3) {
        sakrijRezultateAdrese();
        prikaziToast('Upišite barem 3 znaka adrese.', true);
        $checkoutAddress.trigger('focus');
        return;
      }

      $addressSearchButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span><span>Tražim...</span>');
      $addressHint.text('Pretražujem adresu...');

      $.getJSON(BASE + '?akcija=lokacija_pretrazi', { q: query })
        .done(function (odgovor) {
          const rezultati = odgovor && Array.isArray(odgovor.rezultati) ? odgovor.rezultati : [];
          $addressResults.empty().removeClass('d-none');

          if (!rezultati.length) {
            $addressResults.append('<div class="checkout-geocode-empty">Nema rezultata. Pokušajte upisati ulicu, broj i grad ili ručno postavite pin na karti.</div>');
            $addressHint.text('Upišite ulicu, broj i grad.');
            return;
          }

          rezultati.forEach(function (rezultat) {
            const $item = $('<button type="button" class="checkout-geocode-result"><i class="bi bi-geo-alt-fill"></i><span></span></button>');
            $item.find('span').text(rezultat.naziv || 'Lokacija');
            $item.on('click', function () {
              $checkoutAddress.val(rezultat.naziv || query).trigger('change');
              postaviLokaciju(rezultat.lat, rezultat.lng, true);
              sakrijRezultateAdrese();
              $addressHint.text('Adresa je pronađena i pin je postavljen na karti.');
            });
            $addressResults.append($item);
          });
        })
        .fail(function (xhr) {
          sakrijRezultateAdrese();
          const poruka = xhr && xhr.responseJSON && xhr.responseJSON.greska
            ? xhr.responseJSON.greska
            : 'Pretraga lokacije trenutno nije dostupna.';
          $addressHint.text('Pin i dalje možete postaviti ručno klikom na kartu.');
          prikaziToast(poruka, true);
        })
        .always(function () {
          $addressSearchButton.prop('disabled', false).html('<i class="bi bi-search"></i><span>Pronađi</span>');
        });
    }

    $addressSearchButton.on('click', pretraziAdresu);
    $checkoutAddress.on('keydown', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        pretraziAdresu();
      }
    });

    map.on('click', function (e) {
      postaviLokaciju(e.latlng.lat, e.latlng.lng, false);
    });

    $('#use-current-location').on('click', function () {
      const $statusText = $('#location-button-status');
      if (!navigator.geolocation) {
        $statusText.text('Vaš preglednik ne podržava geolokaciju');
        prikaziToast('Geolokacija nije podržana u ovom pregledniku.', true);
        return;
      }

      $statusText.text('Tražim vašu lokaciju...');
      navigator.geolocation.getCurrentPosition(
        function (position) {
          postaviLokaciju(position.coords.latitude, position.coords.longitude, true);
          $statusText.text('Lokacija je postavljena na karti');
        },
        function () {
          $statusText.text('Lokaciju možete postaviti klikom na kartu');
          prikaziToast('Lokaciju nije moguće dohvatiti. Možete ručno postaviti pin.', true);
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
      );
    });

    window.setTimeout(function () { map.invalidateSize(); }, 150);
  }

  const receiptMapElement = document.getElementById('receipt-delivery-map');
  if (receiptMapElement && window.L) {
    const lat = Number(receiptMapElement.getAttribute('data-lat'));
    const lng = Number(receiptMapElement.getAttribute('data-lng'));
    if (Number.isFinite(lat) && Number.isFinite(lng)) {
      const receiptMap = L.map(receiptMapElement, {
        zoomControl: false,
        dragging: false,
        scrollWheelZoom: false,
        doubleClickZoom: false,
        boxZoom: false,
        keyboard: false,
        tap: false,
      }).setView([lat, lng], 16);
      L.tileLayer('https://maps.geoapify.com/v1/tile/positron/{z}/{x}/{y}.png?apiKey=' + encodeURIComponent(window.GEOAPIFY_API_KEY || ''), {
        maxZoom: 20,
        attribution: '&copy; OpenStreetMap contributors &copy; Geoapify',
      }).addTo(receiptMap);
      L.marker([lat, lng]).addTo(receiptMap);
      window.setTimeout(function () { receiptMap.invalidateSize(); }, 150);
    }
  }

  $(document).on('click', '.js-preuzmi-dostavu', function () {
    const $btn = $(this);
    const narudzbaId = $btn.data('narudzba-id');
    const originalHtml = $btn.html();
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Prihvaćam...');

    $.post(BASE + '?akcija=dostava_prihvati', {
      narudzba_id: narudzbaId,
      csrf_token: CSRF_TOKEN,
    })
      .done(function (odgovor) {
        if (odgovor && odgovor.uspjeh) {
          prikaziToast('Dostava je tvoja. Stanje je sada ZAUZETO.');
          window.setTimeout(function () { window.location.reload(); }, 650);
          return;
        }
        prikaziToast((odgovor && odgovor.greska) || 'Narudžba više nije slobodna.', true);
        window.setTimeout(function () { window.location.reload(); }, 850);
      })
      .fail(function (xhr) {
        const poruka = xhr && xhr.responseJSON && xhr.responseJSON.greska
          ? xhr.responseJSON.greska
          : 'Narudžba više nije slobodna ili je došlo do greške.';
        prikaziToast(poruka, true);
        $btn.prop('disabled', false).html(originalHtml);
        if (xhr && xhr.status === 409) {
          window.setTimeout(function () { window.location.reload(); }, 900);
        }
      });
  });

  $(document).on('click', '.js-admin-assign-delivery', function () {
    const $btn = $(this);
    const $row = $btn.closest('.js-admin-order-row');
    const narudzbaId = parseInt($btn.data('narudzba-id'), 10) || 0;
    const $select = $row.find('.js-admin-driver-select');
    const dostavljacId = parseInt($select.val(), 10) || 0;
    const dostavljacNaziv = $select.find('option:selected').text();

    if (!dostavljacId) {
      prikaziToast('Odaberi dostavljača prije dodjele.', true);
      $select.trigger('focus');
      return;
    }

    const originalHtml = $btn.html();
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
    $select.prop('disabled', true);

    $.post(BASE + '?akcija=dostava_dodijeli', {
      narudzba_id: narudzbaId,
      dostavljac_id: dostavljacId,
      csrf_token: CSRF_TOKEN,
    })
      .done(function (odgovor) {
        if (!(odgovor && odgovor.uspjeh)) {
          prikaziToast((odgovor && odgovor.greska) || 'Dodjela nije uspjela.', true);
          $btn.prop('disabled', false).html(originalHtml);
          $select.prop('disabled', false);
          return;
        }

        $row.find('.js-dispatch-state-cell').html('<span class="dispatch-state dispatch-busy"><i class="bi bi-lock-fill"></i> Zauzeto</span>');
        $row.find('.js-dispatch-assignment-cell').html(
          '<div class="fw-semibold"><i class="bi bi-bicycle text-primary me-1"></i>' + $('<div>').text(dostavljacNaziv).html() + '</div>' +
          '<div class="small text-muted">Narudžba je zaključana za ostale.</div>'
        );
        prikaziToast('Narudžba dodijeljena. Stanje je sada ZAUZETO.');
      })
      .fail(function (xhr) {
        const poruka = xhr && xhr.responseJSON && xhr.responseJSON.greska
          ? xhr.responseJSON.greska
          : 'Dodjela dostavljača nije uspjela.';
        prikaziToast(poruka, true);
        $btn.prop('disabled', false).html(originalHtml);
        $select.prop('disabled', false);
        if (xhr && xhr.status === 409) {
          window.setTimeout(function () { window.location.reload(); }, 1000);
        }
      });
  });

  function posaljiStatusNarudzbe(narudzbaId, noviStatus, $red, $kontrola) {
    $.post(BASE + '?akcija=narudzba_status', {
      narudzba_id: narudzbaId,
      status: noviStatus,
      csrf_token: CSRF_TOKEN,
    })
      .done(function (odgovor) {
        if (odgovor && odgovor.uspjeh) {
          const $pill = $red.find('.js-status-pill');
          $pill.attr('class', 'status-pill js-status-pill status-' + noviStatus).text(noviStatus.replace(/_/g, ' '));
          prikaziToast(noviStatus === 'dostavljena' ? 'Narudžba je označena kao dostavljena.' : 'Status narudžbe je ažuriran.');
          if ($kontrola && ($kontrola.hasClass('js-delivery-status-button') || $kontrola.hasClass('js-restaurant-status-button'))) {
            window.setTimeout(function () { window.location.reload(); }, 500);
          }
        } else {
          prikaziToast((odgovor && odgovor.greska) || 'Status nije moguće promijeniti.', true);
          if ($kontrola) $kontrola.prop('disabled', false);
        }
      })
      .fail(function (xhr) {
        const poruka = xhr && xhr.responseJSON && xhr.responseJSON.greska
          ? xhr.responseJSON.greska
          : 'Status nije moguće promijeniti.';
        prikaziToast(poruka, true);
        if ($kontrola) $kontrola.prop('disabled', false);
      });
  }

  $(document).on('change', '.js-status-narudzbe', function () {
    const $select = $(this);
    const narudzbaId = $select.data('narudzba-id');
    const noviStatus = $select.val();
    posaljiStatusNarudzbe(narudzbaId, noviStatus, $select.closest('.narudzba-red'), $select);
  });

  $(document).on('click', '.js-delivery-status-button, .js-restaurant-status-button', function () {
    const $btn = $(this);
    const narudzbaId = parseInt($btn.data('narudzba-id'), 10) || 0;
    const noviStatus = String($btn.data('status') || '');
    const potvrda = String($btn.data('confirm') || '');
    if (!narudzbaId || !noviStatus) return;
    if (potvrda && !window.confirm(potvrda)) return;
    $btn.prop('disabled', true);
    posaljiStatusNarudzbe(narudzbaId, noviStatus, $btn.closest('.narudzba-red'), $btn);
  });

  $(document).on('click', '[data-restaurant-order-filter]', function () {
    const $btn = $(this);
    const filter = String($btn.attr('data-restaurant-order-filter') || 'all');
    const $buttons = $('[data-restaurant-order-filter]');
    const $cards = $('.restaurant-order-card[data-order-status]');
    let visible = 0;

    $buttons.removeClass('active');
    $btn.addClass('active');

    $cards.each(function () {
      const $card = $(this);
      const status = String($card.attr('data-order-status') || '');
      const group = String($card.attr('data-order-group') || '');
      const show = filter === 'all' || filter === status || filter === group;
      $card.prop('hidden', !show);
      if (show) visible += 1;
    });

    $('#restaurant-filter-empty').toggleClass('d-none', visible > 0);
  });

  const $weatherWidget = $('#weather-widget');
  if ($weatherWidget.length) {
    const grad = $weatherWidget.data('grad') || 'Mostar';
    $.getJSON(BASE + '?akcija=vrijeme&grad=' + encodeURIComponent(grad))
      .done(function (podaci) {
        if (podaci.greska) {
          $weatherWidget.html('<span class="text-muted small">Vrijeme trenutno nedostupno.</span>');
          return;
        }
        let upozorenje = podaci.upozorenje_dostava
          ? '<span class="text-tomato ms-2">⚠ ' + podaci.upozorenje_dostava + '</span>'
          : '';
        $weatherWidget.html(
          '<img src="' + podaci.ikona_url + '" alt="' + podaci.opis + '" width="36" height="36">' +
          '<span><strong>' + podaci.temperatura + '°C</strong> u ' + podaci.grad + ' &middot; ' + podaci.opis + '</span>' +
          upozorenje
        );
      })
      .fail(function () {
        $weatherWidget.html('<span class="text-muted small">Vrijeme trenutno nedostupno.</span>');
      });
  }

  const $datepicker = $('#zeljeno_vrijeme_dostave');
  if ($datepicker.length && $.fn.datepicker) {
    $datepicker.datepicker({
      dateFormat: 'dd.mm.yy',
      minDate: 0,
      firstDay: 1,
      dayNamesMin: ['Ne', 'Po', 'Ut', 'Sr', 'Če', 'Pe', 'Su'],
      monthNames: ['Siječanj','Veljača','Ožujak','Travanj','Svibanj','Lipanj','Srpanj','Kolovoz','Rujan','Listopad','Studeni','Prosinac'],
    });
  }

  const $sortableMeni = $('#sortable-jelovnik');
  if ($sortableMeni.length && $.fn.sortable) {
    $sortableMeni.sortable({ placeholder: 'ui-state-highlight', cursor: 'move' });
    $sortableMeni.disableSelection();
  }

  if (window.tinymce && document.querySelector('.js-wysiwyg')) {
    tinymce.init({
      selector: '.js-wysiwyg',
      height: 260,
      menubar: false,
      plugins: 'lists link',
      toolbar: 'bold italic underline | bullist numlist | link | removeformat',
      branding: false,
    });
  }

  $('.fh-phone[data-phone-restaurants]').each(function () {
    const $phone = $(this);
    let restorani = [];

    try {
      restorani = JSON.parse($phone.attr('data-phone-restaurants') || '[]');
    } catch (e) {
      restorani = [];
    }

    if (!Array.isArray(restorani) || restorani.length === 0) return;

    let trenutni = 0;
    let timer = null;
    const smanjiAnimacije = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const $promo = $phone.find('.fh-promo');
    const $promoImg = $phone.find('.js-phone-promo-image');
    const $promoNaziv = $phone.find('.js-phone-promo-name');
    const $kartica = $phone.find('.js-phone-restaurant-link');
    const $karticaImg = $phone.find('.js-phone-restaurant-image');
    const $karticaNaziv = $phone.find('.js-phone-restaurant-name');
    const $karticaAdresa = $phone.find('.js-phone-restaurant-address');
    const $dots = $phone.find('.js-phone-dot');

    function prikaziRestoran(index) {
      trenutni = ((index % restorani.length) + restorani.length) % restorani.length;
      const restoran = restorani[trenutni];
      if (!restoran) return;

      $promo.addClass('is-switching');
      $kartica.addClass('is-switching');

      window.setTimeout(function () {
        $promoImg.attr({ src: restoran.slika, alt: restoran.naziv });
        $promoNaziv.text(restoran.naziv);
        $kartica.attr('href', restoran.url || '#');
        $karticaImg.attr({ src: restoran.slika, alt: restoran.naziv });
        $karticaNaziv.text(restoran.naziv);
        $karticaAdresa.text(restoran.adresa || 'Mostar');
        $dots.removeClass('active').attr('aria-current', 'false');
        $dots.filter('[data-phone-index="' + trenutni + '"]').addClass('active').attr('aria-current', 'true');
        $promo.removeClass('is-switching');
        $kartica.removeClass('is-switching');
      }, smanjiAnimacije ? 0 : 150);
    }

    function zaustaviRotaciju() {
      if (timer) {
        window.clearInterval(timer);
        timer = null;
      }
    }

    function pokreniRotaciju() {
      zaustaviRotaciju();
      if (smanjiAnimacije || restorani.length < 2) return;
      timer = window.setInterval(function () {
        prikaziRestoran(trenutni + 1);
      }, 3800);
    }

    $dots.on('click', function () {
      prikaziRestoran(parseInt($(this).attr('data-phone-index'), 10) || 0);
      pokreniRotaciju();
    });

    $phone.on('mouseenter focusin', zaustaviRotaciju);
    $phone.on('mouseleave', pokreniRotaciju);
    $phone.on('focusout', function (e) {
      if (!this.contains(e.relatedTarget)) pokreniRotaciju();
    });

    prikaziRestoran(0);
    pokreniRotaciju();
  });

  $(document).on('change', '.js-auto-submit-category', function () {
    const form = this.form;
    if (form) form.submit();
  });

  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
    new bootstrap.Tooltip(el);
  });

})(jQuery);
