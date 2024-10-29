(function( $ ) {
	'use strict';

  $(document).ready(function() {

    $('.cdn-option input[type=checkbox], .cdn-option input[type=radio]').on('click', function(e) {
      e.preventDefault()
      showModalLoader()
      var form_type = $('form.arvancloud-options-form').attr('data-type');
      if (form_type == 'acceleration') {
        var item = {
          name: $(this).attr('name'),
          status: $(this).is(":checked"),
          label: $(this).parent().parent().find('h3').html(),
          js: $('#js_optimization').is(":checked"),
          css: $('#css_optimization').is(":checked"),
        };
      } else if (form_type == 'ddos_protection') {
        var ele = $('input[name="ddos_protection_mode"]:checked');
        var item = {
          name:   $(ele).attr('name'),
          value:  $(ele).val(),
          label:  $('form.arvancloud-options-form').parent().find('h2').html(),
        };
      } else if (form_type == 'cache_status') {
        var ele = $('input[name="cache_status"]:checked');
        var item = {
          name:   $(ele).attr('name'),
          value:  $(ele).val(),
          label:  $('form.arvancloud-options-form').parent().find('h2').html(),
        };
      } else {
        var item = {
          name:       $(this).attr('name'),
          status:     $(this).is(":checked"),
          label:      $(this).parent().parent().find('h3').html(),
          form_type:  form_type,
        };
      }
      start(item, $(this))
    })

    $('.arvancloud-ddos-protection-options select.ar-dropdown[name=ttl]').on('change', function(e) {
      e.preventDefault()
      showModalLoader()
      var form_type = $('form.arvancloud-options-form').attr('data-type');
      var item = {
        name:       $(this).attr('name'),
        value:      $(this).val(),
        label:      $(this).parent().find('label').html(),
        form_type:  form_type,
      };

      start(item, $(this))
    })

    $('.edit-settings').on('click', function(e) {
      e.preventDefault()
      editHSTS()
    })

    $('.edit-settings-cache').on('click', function(e) {
      e.preventDefault()
      editCache()
    })

    $('.side-modal-HSTS .side-modal-heading .close, .side-modal-HSTS .ar-cancel-modal').on('click', function(e) {
      e.preventDefault()
      hideHSTS()
    })


    $('#report_period').on('change', function() {
      const urlParams = new URLSearchParams(window.location.search);
      urlParams.set('period', $(this).val());
      window.location.search = urlParams;
    })

    $('#ar-https-agreement').on('change', function() {
      if($(this).is(":checked")) {
        EnableHSTSEdit()
      } else {
        resetHSTSEdit()
      }
    })


    $('.side-modal-HSTS .ar-submit-modal').on('click', function(e) {
      e.preventDefault()
      if ($(this).hasClass('disabled')) {
        return
      }

      var form_type = $('form.arvancloud-options-form').attr('data-type');


      showModalLoader()
      var items = {
        name:           'edit_hsts',
        hsts_status:    $('input[name="hsts_status"]').is(":checked"),
        hsts_max_age:   $('select[name="hsts_max_age"]').val(),
        hsts_subdomain: $('input[name="hsts_subdomain"]').is(":checked"),
        hsts_preload:   $('input[name="hsts_preload"]').is(":checked"),
        label:          $('input[name="hsts_status"]').parent().parent().parent().parent().find('h3').html(),
        form_type:      form_type,
      };
      start(items, $(this))

    })

    $('.side-modal-cache .side-modal-heading .close, .side-modal-cache .ar-cancel-modal').on('click', function(e) {
      e.preventDefault()
      hideCache()
    })

    $('.side-modal-cache .ar-submit-modal').on('click', function(e) {
      e.preventDefault()
      if ($(this).hasClass('disabled')) {
        return
      }

      var form_type = $('form.arvancloud-options-form').attr('data-type');


      showModalLoader()
      var items = {
        name:           'edit_cache',
        cache_args:    $('input[name="cache_args"]').is(":checked"),
        cache_scheme:    $('input[name="cache_scheme"]').is(":checked"),
        cache_arg:   $('input[name="cache_arg"]').val(),
        cache_cookie:   $('input[name="cache_cookie"]').val(),
        label:          $('.side-modal-cache .side-modal-heading h3').html(),
        form_type:      form_type,
      };
      start(items, $(this))

    })
  })

  function save_options(item, ele) {

    var form_type = $('form.arvancloud-options-form').attr('data-type');

    if (form_type == 'cdn_options') {
      var ar_ajax_action = 'ar_cdn_options';
    } else if (form_type == 'acceleration') {
      var ar_ajax_action = 'ar_acceleration_options';
    } else if (form_type == 'ddos_protection') {
      var ar_ajax_action = 'ar_ddos_protection_options';
    } else if (form_type == 'https') {
      var ar_ajax_action = 'ar_https_options';
    } else if (form_type == 'firewall') {
      var ar_ajax_action = 'ar_firewall';
    } else if (form_type == 'cache_status') {
      var ar_ajax_action = 'ar_cache_status';
    }

    $.ajax({
      url: ar_cdn_ajax_object.ajax_url,
      data: {
        'action': ar_ajax_action,
        'security': ar_cdn_ajax_object.security,
        'option_item': item,
      },
      success:function(data) {
        $(ele).prop("checked", !$(ele).prop("checked"));
        toastr.remove();
        showToastr('success', item.label + ' ' + ar_cdn_ajax_object.strings.updated, '');

        if (item.name == 'ssl_status') {
          disableFormBasedOnHTTPS()
        } else if (item.name == 'https_redirect') {
          updateHSTSProtocolinput()
        }

        if (form_type == 'ddos_protection') {
          var selected_mode = $('input[name="ddos_protection_mode"]:checked').val();
          if ( selected_mode == 'cookie' ) {
            $('.cookie-ttl-wrapper').show()
            $('.js-ttl-wrapper').hide()
            $('.recaptcha-ttl-wrapper').hide()
          } else if (selected_mode == 'javascript') {
            $('.cookie-ttl-wrapper').hide()
            $('.js-ttl-wrapper').show()
            $('.recaptcha-ttl-wrapper').hide()
          } else if (selected_mode == 'recaptcha') {
            $('.cookie-ttl-wrapper').hide()
            $('.js-ttl-wrapper').hide()
            $('.recaptcha-ttl-wrapper').show()
          } else {
            $('.cookie-ttl-wrapper').hide()
            $('.js-ttl-wrapper').hide()
            $('.recaptcha-ttl-wrapper').hide()
          }
        } else if (form_type == 'cache_status') {
          var selected_mode = $('input[name="cache_status"]:checked').val();
          if ( selected_mode == 'advance' ) {
            $('.cache-mode-advance-options').show()
          } else {
            $('.cache-mode-advance-options').hide()
          }
        }

        hideModalLoader()
      },
      error: function(errorThrown){
        toastr.remove();
        if (item.name == "ttl") {
          $(".arvancloud-ddos-protection-options select.ar-dropdown[name=ttl] option:first").attr('selected','selected');
        }
        var message = ( 'responseJSON' in errorThrown && 'data' in errorThrown.responseJSON && errorThrown.responseJSON.data != '') ? errorThrown.responseJSON.data : ar_cdn_ajax_object.strings.failed;
        showToastr('error', message, '');
        hideModalLoader()
      }
    })
      
  }

  function start(item, ele){
    showToastr("info", ar_cdn_ajax_object.strings.wait, ar_cdn_ajax_object.strings.sent);
    setTimeout(save_options(item, ele), 3000); // Setting arbitrary timeout here so we can see the 'loading' state.
  };

  function updateHSTSProtocolinput() {
    if ($('#https_redirect').is(':checked')) {
      $('#HSTS').removeClass('disabled')
    } else {
      $('#HSTS').addClass('disabled')
    }
  }

  function disableFormBasedOnHTTPS() {
    var ssl_status = $('.cdn-option input#ssl_status').is(':checked')
    if (ssl_status) {
      $('.cdn-option input[type=checkbox]:not(#ssl_status)').parent().parent().removeClass('disabled')
    } else {
      $('input#https_redirect').prop('checked', false);
      $('input#replace_http').prop('checked', false);
      $('.cdn-option input[type=checkbox]:not(#ssl_status)').parent().parent().addClass('disabled')
    }

    updateHSTSProtocolinput();
  }

  function hide_ar_modal(e) {
    if($('body').hasClass('rtl')) {
      $(e).css('left', '-500px')
    } else {
      $(e).css('right', '-500px')
    }
  }

  function show_ar_modal(e) {
    if($('body').hasClass('rtl')) {
      $(e).css('left', '0')
    } else {
      $(e).css('right', '0')
    }
  }

  function editHSTS() {
    show_ar_modal($('.side-modal-HSTS'))
  }

  function editCache() {
    show_ar_modal($('.side-modal-cache'))
  }

  function hideHSTS() {
    //reset data and hide modal
    resetHSTSEdit()
    hide_ar_modal($('.side-modal-HSTS'));
  }

  function hideCache() {
    // hide modal
    hide_ar_modal($('.side-modal-cache'));
  }

  function resetHSTSEdit() {
    var fields = [
      'hsts_status',
      'hsts_subdomain',
      'hsts_preload'
    ];


    fields.map((name) => {
      var e = 'input' + '[name=' + name + ']';
      $(e).parent().parent().addClass('disabled')
    })

    $('.ar-submit-modal').addClass('disabled')
    $('select[name=hsts_max_age]').parent().addClass('disabled')
  }

  function EnableHSTSEdit() {
    var fields = [
      'hsts_status',
      'hsts_subdomain',
      'hsts_preload'
    ];


    fields.map((name) => {
      var e = 'input' + '[name=' + name + ']';
      $(e).parent().parent().removeClass('disabled')
    })

    $('.ar-submit-modal').removeClass('disabled')
    $('select[name=hsts_max_age]').parent().removeClass('disabled')
  }




})( jQuery );


function showModalLoader() {
  jQuery('#lock-modal').show()
  jQuery('#loading-circle').show()
}

function hideModalLoader() {
  jQuery('#lock-modal').hide()
  jQuery('#loading-circle').hide()
}

function showToastr(type, title, message) {
  toastr.options = {
    closeButton: true,
    debug: false,
    newestOnTop: false,
    progressBar: false,
    positionClass: "toast-bottom-right",
    preventDuplicates: true,
    showDuration: 500,
    hideDuration: 500,
    timeOut: 0,
    onclick: null,
    onCloseClick: null,
    extendedTimeOut: 0,
    showEasing: "swing",
    hideEasing: "linear",
    showMethod: "fadeIn",
    hideMethod: "fadeOut",
    tapToDismiss: false
  };

  if (ar_cdn_ajax_object.is_rtl) {
    toastr.options['positionClass'] = "toast-bottom-left";
  }

  toastr[type](message, title, { timeOut: 3000 })
}