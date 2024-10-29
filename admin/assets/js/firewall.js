/**
 * (B1) Set <li draggable>.
 * (B2) On drag start, attach .hint to highlight all the list items.
 * (B3) When the dragged element hovers a list item, add .active to show a different highlight color.
 * (B4) When the dragged element leaves a list item, remove .active.
 * (B5) When the drag stops, remove all .hint and .active CSS classes.
 * (B6) Necessary. Prevents the default browser action, so we can define our own.
 * (B7) Some Math. Does the actual sorting on dropped.
 */
function slist (target) {
  // (A) SET CSS + GET ALL LIST ITEMS
  target.classList.add("slist");
  let items = jQuery(target).children("tbody").children("tr"), current = null;

  // (B) MAKE ITEMS DRAGGABLE + SORTABLE
  for (let i of items) {
    // (B1) ATTACH DRAGGABLE
    i.draggable = true;

    // (B2) DRAG START - YELLOW HIGHLIGHT DROPZONES
    i.ondragstart = (ev) => {
      current = i;
      for (let it of items) {
        if (it != current) { it.classList.add("hint"); }
      }
    };

    // (B3) DRAG ENTER - RED HIGHLIGHT DROPZONE
    i.ondragenter = (ev) => {
      if (i != current) { i.classList.add("active"); }
    };

    // (B4) DRAG LEAVE - REMOVE RED HIGHLIGHT
    i.ondragleave = () => {
      i.classList.remove("active");
    };

    // (B5) DRAG END - REMOVE ALL HIGHLIGHTS
    i.ondragend = () => { for (let it of items) {
      it.classList.remove("hint");
      it.classList.remove("active");
    }};

    // (B6) DRAG OVER - PREVENT THE DEFAULT "DROP", SO WE CAN DO OUR OWN
    i.ondragover = (evt) => { evt.preventDefault(); };

    // (B7) ON DROP - DO SOMETHING
    i.ondrop = (evt) => {
      evt.preventDefault();
      if (i != current) {
        showModalLoader()
        let currentpos = 0, droppedpos = 0, mode, otherItem_rule_id;
        otherItem_rule_id = jQuery(i).attr('data-rule_id')
        for (let it=0; it<items.length; it++) {
          if (current == items[it]) { currentpos = it; }
          if (i == items[it]) { droppedpos = it; }
        }
        if (currentpos < droppedpos) {
          i.parentNode.insertBefore(current, i.nextSibling);
          rule_id = jQuery(i.nextSibling).attr('data-rule_id')
          mode = 'after';
        } else {
          i.parentNode.insertBefore(current, i);
          rule_id = jQuery(i.previousSibling).attr('data-rule_id')
          mode = 'before';
        }
        change_rules_priority(rule_id, otherItem_rule_id, mode)
        hideModalLoader()
      }
    };
  }
}

function edit_firewall() {
  if(jQuery('body').hasClass('rtl')) {
    jQuery('.side-modal-firewall').css('left', '0')
  } else {
    jQuery('.side-modal-firewall').css('right', '0')
  }
}

function hide_firewall() {
  reset_firewall_Edit()
  if(jQuery('body').hasClass('rtl')) {
    jQuery('.side-modal-firewall').css('left', '-100%')
  } else {
    jQuery('.side-modal-firewall').css('right', '-100%')
  }
}

function reset_firewall_Edit() {
  $form = jQuery('.side-modal-firewall form');
  $form.attr('data-action', 'add_rule')
  $form.removeAttr('data-rule_id')
  jQuery(".side-modal-firewall select[name=action]").val('allow').trigger('change');
  jQuery(".side-modal-firewall input[name=note]").val('')
  jQuery(".side-modal-firewall input[name=name]").val('')
  jQuery("#export_filter").val('')

  jQuery(".ar-firewall-or").remove()
  jQuery('#ar-firewall-or').trigger('click');
  jQuery(".c-cdnFirewallRules__orChipsContainer").remove()
}

jQuery(document).ready(function() {

  /**
   * Add a Rule
   */
  jQuery('#add_firewall_rules').on('click', function(e) {
    e.preventDefault();
    jQuery('.side-modal-firewall .side-modal-heading .edit_rule').hide()
    jQuery('.side-modal-firewall .side-modal-heading .create_rule').show()
    reset_firewall_Edit();
    edit_firewall()
  })
  
  /**
   * Edit a Rule
   */
  jQuery('#firewall_rules').on('click', '.firewall_rule td button[data-action=edit]', function(e) {
    e.preventDefault();
    showModalLoader()
    var form_type = jQuery('form.arvancloud-options-form').attr('data-type');
    let rule_id = jQuery(this).parent().parent().attr('data-rule_id')

    if (form_type != 'firewall') {
      return
    }
  
    jQuery.ajax({
      url: ar_cdn_ajax_object.ajax_url,
      data: {
        'action'  : 'ar_firewall_get_rule',
        'security': ar_cdn_ajax_object.security,
        'option_item': {id: rule_id},
      },
      success:function(data) {
        if (data.data.id != '') {
          
          jQuery('.side-modal-firewall .side-modal-heading .edit_rule').show()
          jQuery('.side-modal-firewall .side-modal-heading .create_rule').hide()

          jQuery('.firewall_rule_repeater .ar-firewall-or').remove();
          jQuery('.firewall_rule_repeater .c-cdnFirewallRules__orChipsContainer').remove();
  
          $form = jQuery('.side-modal-firewall form');
          $form.attr('data-action', 'edit_rule')
          $form.attr('data-rule_id', data.data.id)
          
          jQuery(".side-modal-firewall input[name=name]").val(data.data.name)
          jQuery(".side-modal-firewall input[name=note]").val(data.data.note)
          jQuery(".side-modal-firewall select[name=action]").val(data.data.action).trigger('change');
          data.data.is_enabled = data.data.is_enabled ? '1' : '0'
          jQuery(".side-modal-firewall select[name=is_enabled]").val(data.data.is_enabled).trigger('change');

          var filter_arr = convert_filter_to_filter(data.data.filter_expr);

          filter_arr.forEach(function(filter) {
            jQuery('#ar-firewall-or').trigger('click');
            if (jQuery('.firewall_rule_repeater > div:first-child').hasClass('c-cdnFirewallRules__orChipsContainer')) {
              jQuery('.firewall_rule_repeater > div:first-child').remove();
            }
            var current_or = jQuery('.firewall_rule_repeater').children('.ar-firewall-or').last();
            current_or.children('.ar-firewall-and').last().remove();
            filter.forEach(function(filter_item) {
              current_or.find('#ar-firewall-add').trigger('click');
              var current_and = current_or.children('.ar-firewall-and').last();

              jQuery('.ar-dropdown-select2[name="filter_type"]').select2({
                data: convertObjectToArray(ar_cdn_ajax_object["rule_filter_type"]),
                placeholder: jQuery(this).attr('data-placeholder'),
                allowClear: true,
                minimumResultsForSearch: -1
              })

              current_and.find('select[name="filter_type"').val(filter_item[0]).trigger('change').trigger('select2:select');
              current_and.find('select[name="filter_operator"').val(filter_item[1]).trigger('change').trigger('select2:select');

              var filter_value = current_and.find('input[name="filter_value"');
              if (filter_item[2] instanceof Array) {
                jQuery(filter_value).replaceWith('<select class="ar-dropdown ar-dropdown-country form-control" name="filter_value"></select>')
                filter_value = current_and.find('[name="filter_value"]')
                
                if (filter_item[1] == 'in' && filter_item[0] != 'ip.geoip.country') {
                  var option_data = filter_item[2].map(function(item) {
                    return {id: item, text: item}
                  })
                  jQuery(filter_value).select2({
                    tags: true,
                    allowClear: true,
                    minimumResultsForSearch: -1,
                    data: option_data
                  })
                  current_and.find('select[name="filter_value"').val(filter_item[2]).trigger('change').trigger('select2:select');


                } else {
                  current_and.find('select[name="filter_value"').val(filter_item[2]).trigger('change').trigger('select2:select');
                }

              } else {
                current_and.find('input[name="filter_value"').val(filter_item[2].replace(/^"(.*)"$/, '$1')).trigger('change');
              }

            })

            reset_firewall_rule_remove_buttons()
          })

          

          edit_firewall()
        }
        
        hideModalLoader()
      },
      error: function(errorThrown){
        toastr.remove();
        var message = ( 'responseJSON' in errorThrown && 'data' in errorThrown.responseJSON && errorThrown.responseJSON.data != '') ? errorThrown.responseJSON.data : ar_cdn_ajax_object.strings.failed;
        showToastr('error', message, '');
        hideModalLoader()
      }
    })
  })


  /**
   * Delete a rule
  */
  jQuery('#firewall_rules').on('click', '.firewall_rule td button[data-action=delete]', function(e) {
    e.preventDefault();
    showModalLoader()
    let rule_id = jQuery(this).parent().parent().attr('data-rule_id')
    send_ajax_req_firewall( {id: rule_id, action: 'delete'}, 'ar_firewall_delete_rule')
  })


  jQuery('.side-modal-firewall .side-modal-heading .close, .side-modal-firewall .ar-cancel-modal').on('click', function(e) {
    e.preventDefault()
    hide_firewall()
  })

  jQuery(".ar-dropdown-ip").select2({
    tags: true,
    tokenSeparators: [',', ' '],
    placeholder: jQuery(this).attr('data-placeholder'),
    allowClear: true
  })

  jQuery(".ar-dropdown-country").select2({
    placeholder: jQuery(this).attr('data-placeholder'),
    allowClear: true
  });
  jQuery(".ar-dropdown-select2").select2({
    width: '100%',
    placeholder: jQuery(this).attr('data-placeholder'),
    allowClear: true,
    minimumResultsForSearch: -1
  });



  jQuery('.side-modal-firewall .ar-submit-modal').on('click', function(e) {
    e.preventDefault()
    if (jQuery(this).hasClass('disabled')) {
      return
    }
    showModalLoader()

    let action_type = jQuery('.side-modal-firewall form').attr('data-action')
    let form_type = jQuery('form.arvancloud-options-form').attr('data-type');
    let item = {
      type:           action_type,
      filter_expr:    jQuery('input[name="export_filter"]').val(),
      name:           jQuery('input[name="name"]').val(),
      note:           jQuery('input[name="note"]').val(),
      action:         jQuery('select[name="action"]').val(),
      is_enabled:     jQuery('select[name="is_enabled"]').val(),
      form_type:      form_type,
      action_type:    action_type
    };

    if (item.filter_expr == '' || item.name == '' || item.action == '') {
      showToastr('error', ar_cdn_ajax_object.strings.failed, '');

      return;
    }

    if (action_type == 'edit_rule') {
      item['id'] = jQuery('.side-modal-firewall form').attr('data-rule_id');
      send_ajax_req_firewall(item, 'ar_firewall_update_rule')
    } else if (action_type == 'add_rule') {
      send_ajax_req_firewall(item, 'ar_firewall_create_rule')
    }

    hide_firewall()
  })

  jQuery(document).on('click', '.firewall_rule_row--delete button[data-action=delete]', function(e) {
    e.preventDefault();

    var or_div = jQuery(this).parent().parent().parent().parent();
    var and_div = jQuery(this).parent().parent().parent();

    var all_and_exist_in_or = or_div.children('.ar-firewall-and')    
    if (jQuery('.firewall_rule_repeater > .ar-firewall-or:first-child')[0] == or_div[0] && jQuery(or_div).children('.ar-firewall-and').length <= 1) {
      // clicked on the first or
      jQuery(or_div).next('.c-cdnFirewallRules__orChipsContainer').remove()
    }


    if (all_and_exist_in_or[0] == and_div[0]) {
      // clicked on the first and
      or_div.children('.firewall-divider').first().remove()
      and_div.remove()
      reset_firewall_rule_remove_buttons()
      return
    }

    and_div.prev('.firewall-divider').remove()
    and_div.prev('.ar-firewall-and').children().first().children('.c-cdnFirewallRules__fixDividerIssue').remove()
    and_div.remove()

    reset_firewall_rule_remove_buttons()
  })

  jQuery(document).on('click', '#ar-firewall-add', function(e) {
    e.preventDefault()
    jQuery(this).parent().parent().children('.ar-firewall-and').last().children().first().append('<div class="c-cdnFirewallRules__fixDividerIssue"><div class="c-cdnFirewallRules__fixDividerIssue__chipsDevider"></div></div>')

    jQuery(this).parent().before('<div class="ar-col-12 firewall-divider"><div class="c-cdnFirewallRules__andChipsContainer"><div class="c-cdnFirewallRules__chipsDevider"></div> <div class="c-cdnFirewallRules__andChips">AND</div> <div class="c-cdnFirewallRules__chipsDevider"></div></div></div><div class="ar-firewall-and"><div class="ar-col-12 ar-col-md-12 ar-col-lg-3"><select class="ar-dropdown ar-dropdown-select2 form-control" data-placeholder="" name="filter_type"></select></div><div class="ar-col-12 ar-col-md-12 ar-col-lg-3"><select class="ar-dropdown ar-dropdown-select2 form-control" data-placeholder="" name="filter_operator"><option></option></select></div><div class="ar-col-12 ar-col-md-12 ar-col-lg-6 filter_value"><input type="text" class="form-control" name="filter_value"></div></div>')

    jQuery('.ar-dropdown-select2[name="filter_type"]').select2({
      data: convertObjectToArray(ar_cdn_ajax_object["rule_filter_type"]),
      placeholder: jQuery(this).attr('data-placeholder'),
      allowClear: true,
      minimumResultsForSearch: -1
    })

    reset_firewall_rule_remove_buttons();
    
  })

  jQuery('#ar-firewall-or').on('click', function(e) {
    e.preventDefault()

    jQuery(this).parent().before(`<div class="c-cdnFirewallRules__orChipsContainer"><div>OR</div></div><div class="ar-firewall-or"><div class="ar-firewall-or-label"><div class="ar-col-12 ar-col-md-12 ar-col-lg-3">${ar_cdn_ajax_object.rule_labels[0]}</div><div class="ar-col-12 ar-col-md-12 ar-col-lg-3">${ar_cdn_ajax_object.rule_labels[1]}</div><div class="ar-col-12 ar-col-md-12 ar-col-lg-6">${ar_cdn_ajax_object.rule_labels[2]}</div></div><div class="ar-firewall-and"><div class="ar-col-12 ar-col-md-12 ar-col-lg-3"><select class="ar-dropdown ar-dropdown-select2 form-control" data-placeholder="" name="filter_type"></select></div><div class="ar-col-12 ar-col-md-12 ar-col-lg-3"><select class="ar-dropdown ar-dropdown-select2 form-control" data-placeholder="" name="filter_operator"><option></option></select></div><div class="ar-col-12 ar-col-md-12 ar-col-lg-6 filter_value"><input type="text" class="form-control" name="filter_value"></div></div><div class="ar-col-12"><button id="ar-firewall-add" class="ar-btn-secondary">`+ ar_cdn_ajax_object.strings.and +'</button></div></div>')

    jQuery('.ar-dropdown-select2[name="filter_type"]').select2({
      data: convertObjectToArray(ar_cdn_ajax_object["rule_filter_type"]),
      placeholder: jQuery(this).attr('data-placeholder'),
      allowClear: true,
      minimumResultsForSearch: -1
    })

    reset_firewall_rule_remove_buttons();
    
  })

  jQuery(document).on('select2:select', 'select[name="filter_type"], select[name="filter_operator"], select[name="filter_value"], input[name="filter_value"]', function() {
    update_rule_export_filter()
  })

  jQuery(document).on('keydown', 'input[name="filter_value"]', function() {
    update_rule_export_filter()
  })


  jQuery(document).on('select2:select', 'select[name="filter_type"]', function (e) {
    var id = jQuery(this).val();
    var filter_value = jQuery(this).parent().parent().find('[name="filter_value"]')
    var filter_operator = jQuery(this).parent().parent().find('.ar-dropdown[name="filter_operator"]')

    if (!jQuery(filter_operator).hasClass("select2-hidden-accessible")) {
      filter_operator.select2();
    }

    if ( ar_cdn_ajax_object["rule_options"][id] != null ) {
      filter_operator.select2('destroy').empty().select2({
        data: convertObjectToArray(ar_cdn_ajax_object["rule_options"][id]),
        allowClear: true,
        minimumResultsForSearch: -1
      });
  
    }
    jQuery(filter_value).val(null).trigger('change');

    if (id == 'ip.geoip.country') {
      if (jQuery(filter_value).hasClass("select2-hidden-accessible")) {
        filter_value.select2('destroy').empty();
      }
      jQuery(filter_value).replaceWith('<select class="ar-dropdown ar-dropdown-country form-control" name="filter_value"></select>')
      filter_value = jQuery(this).parent().parent().find('[name="filter_value"]')
      jQuery(filter_value).empty().select2({
        data: convertObjectToArray(ar_cdn_ajax_object["list_of_countries"]),
        allowClear: true,
        minimumResultsForSearch: -1
      });
    } else {
      if (jQuery(filter_value).hasClass("select2-hidden-accessible")) {
        jQuery(filter_value).select2('destroy').empty()
      }
      jQuery(filter_value).replaceWith('<input type="text" class="form-control" name="filter_value">')
    }
  });

  jQuery(document).on('select2:select', 'select[name="filter_operator"]', function (e) {
    var id = jQuery(this).val();
    var $filter_value = jQuery(this).parent().parent().find('[name="filter_value"]')
    var $filter_type = jQuery(this).parent().parent().find('.ar-dropdown[name="filter_type"]')
    if ( id == 'in' ) {
      if (!$filter_value.hasClass("select2-hidden-accessible")) {
        $filter_value.replaceWith('<select class="ar-dropdown form-control" name="filter_value"></select>')
        var $filter_value = jQuery(this).parent().parent().find('[name="filter_value"]')
        $filter_value.empty().select2({
          minimumResultsForSearch: -1,
        });
      }

      $filter_value.val(null).trigger('change');
      $filter_value.select2({
        multiple: true,
      })

      if ( $filter_type.val() != 'ip.geoip.country' ) {
        $filter_value.select2({
          tags: true,
        })
      }
    } else {
      // reset the value input
      if ($filter_type.val() == 'ip.geoip.country') {
        $filter_value.select2({
          tags: false,
          multiple: false,
        })
        return
      }
      if ($filter_value.hasClass("select2-hidden-accessible")) {
        $filter_value.select2('destroy').empty()
      }
      $filter_value.replaceWith('<input type="text" class="form-control" name="filter_value">')
    }
  })

})

  

function reset_firewall_rules_counter() {
  let counter = 1
  jQuery('#firewall_rules .firewall_rule td.rule-counter').each(function() {
    jQuery(this).html(counter)
    counter++;
  })
}

function change_rules_priority(e1, e2, mode) {
  var form_type = jQuery('form.arvancloud-options-form').attr('data-type');

  if (form_type != 'firewall') {
    return
  }

  var item = {
    mode: mode,
    e1  : e1,
    e2  : e2,
    form_type: form_type,
  };

  send_ajax_req_firewall(item, 'ar_firewall_change_rules_priority')
  slist(document.getElementById("firewall_rules"));
}

function send_ajax_req_firewall(item, action, notice = true) {

  jQuery.ajax({
    url: ar_cdn_ajax_object.ajax_url,
    data: {
      'action'  : action,
      'security': ar_cdn_ajax_object.security,
      'option_item': item,
    },
    success:function(data) {
      if (notice) {
        toastr.remove();
        var message = ( data.data != '') ? data.data : item.label + ' ' + ar_cdn_ajax_object.strings.updated;
        if (action == 'ar_firewall_create_rule') message = JSON.parse(data.data.data)['message'];
        showToastr('success', message, '');
      }

      if (item.action == 'delete') {
        remove_rule(item.id)
      }

      // update row
      if (item.action_type == 'edit_rule' || item.action_type == 'add_rule') {
        location.reload();
      } else if (item.action_type == 'edit_rule') {
        var Item = jQuery('#firewall_rules .firewall_rule[data-rule_id='+ item.id +']')
      }
      // hideModalLoader()
    },
    error: function(errorThrown){
      toastr.remove();
      var message = ( 'responseJSON' in errorThrown && 'data' in errorThrown.responseJSON && errorThrown.responseJSON.data != '') ? errorThrown.responseJSON.data : ar_cdn_ajax_object.strings.failed;
      showToastr('error', message, '');
      hideModalLoader()
    }
  })
}

function remove_rule(rule_id) {
  jQuery('#firewall_rules .firewall_rule[data-rule_id=' + rule_id + ']').remove()
}

window.addEventListener("DOMContentLoaded", () => {
  if (jQuery('#firewall_rules').length) {
    slist(document.getElementById("firewall_rules"));
  }
});

function convertObjectToSelectOptions(obj){
  var htmlTags = '';
  for (var tag in obj){
      htmlTags += '<option value="'+tag+'" selected="selected">'+obj[tag]+'</option>';
  }
  return htmlTags;
}

function convertObjectToArray(obj){
  var arr = [];
  for (var tag in obj){
      arr.push({'id':tag, 'text':obj[tag]});
  }
  return arr;
}

// map Wireshark-like filter expression
function convert_filter_to_filter(filter) {

  var filter_parts = filter.split(' or ');
  if (filter_parts.length >= 2) {
    filter_parts = filter.slice(1).slice(0, -1).trim().replace(/\\\//g, '').split(' or ');

    filter_parts.forEach(function(part, index) {
      var temp = part.trim().slice(1).slice(0, -1);

      filter_parts[index] = temp.split(' and ');
      filter_parts[index].forEach(function(part2, index2) {
        if (part2.indexOf('(') == 0) {
          part2 = part2.slice(1).slice(0, -1);
        }
        filter_parts[index][index2] = part2.replace(/" "/g, ',').split(' ');
        if (filter_parts[index][index2].length == 3 && filter_parts[index][index2][2].indexOf('{') == 0) {
          filter_parts[index][index2][2] = filter_parts[index][index2][2].replace('{', '').replace('}', '').replace(/["']/g, "").split(',');
        }
      });


    })

    
  } else {
    filter_parts = filter.slice(1).slice(0, -1).trim().replace(/\\\//g, '').split(' and ');
    filter_parts.forEach(function(part2, index2) {
      if (part2.indexOf('(') == 0) {
        part2 = part2.slice(1).slice(0, -1);
      }
      filter_parts[index2] = part2.replace(/" "/g, ',').split(' ');
      if (filter_parts[index2].length == 3 && filter_parts[index2][2].indexOf('{') == 0) {
        filter_parts[index2][2] = filter_parts[index2][2].replace('{', '').replace('}', '').split(',');
        filter_parts[index2][2].forEach(function(part3, index3) {
          filter_parts[index2][2][index3] = part3.replace(/["']/g, "");
        })
      }
    });

    filter_parts = [filter_parts];
  }

  return filter_parts;

}

// convert array to Wireshark-like filter expression
function convert_filter_to_filter_array(filter) {
  var filter_parts = [];
  filter.forEach(function(part) {
    var parts = [];
    part.forEach(function(p) {
      if (p[2] instanceof Array) {
        p[2] = '"' + p[2].join('" "') + '"';
        p[2] = '{' + p[2] + '}';
      }
      maybe_push_with_prentices(parts, p.join(' '), false);
    });
    // filter_parts.push(parts.join(') and ('));
    maybe_push_with_prentices(filter_parts, parts, ' and ');
  });



  if (filter_parts.length == 1) {
    return filter_parts.join( ' or ');
  } else {
    return '(' + filter_parts.join( ' or ') + ')' ;
  }
}

function maybe_push_with_prentices(arr, value, joiner) {
  var temp = '';
  if ( value instanceof Array) {
    value.forEach(function(part) {
      temp += '(' + part  + ')';

      // if part has a joiner and it's not the last part
      if (joiner && value.indexOf(part) != value.length - 1) {
        temp += joiner;
      }
    });
  } else {
    temp = value;
  }
  if (joiner == ' and ' && temp.indexOf(' and ') != -1) {
    arr.push('(' + temp + ')');
    return;
  }
  arr.push(temp);

  return arr;
}

function calculate_firewall_rule_and( and_row ) {
  var d_type, d_operator, d_value;
  var $and = jQuery(and_row);
  d_type = $and.find('select[name="filter_type"]').val();
  d_operator = $and.find('select[name="filter_operator"]').val();

  d_value = $and.find('input[name="filter_value"]');
  if (d_value.length < 1) {
    d_value = $and.find('select[name="filter_value"]');
  }
  d_value = jQuery(d_value).val();

  // if d_type and d_value are empty, return false
  if (d_type == '' || d_operator == '' || d_value == '') {
    return false;
  }

  if (d_type != 'ip.src', d_operator != 'in') {
    d_value = `"${d_value}"`;
  }

  return [
    d_type,
    d_operator,
    d_value
  ]
}

function calculate_firewall_rule_or( or_div ) {
  var $or = jQuery(or_div);
  var or_row = [];

  
  $or.children('.ar-firewall-and').each(function(index, and_row) {
    var and_row = calculate_firewall_rule_and( and_row );
    if (and_row) {
      or_row.push(and_row);
    }
  });

  if (or_row.length == 0) {
    return false;
  }

  return or_row;
}

function calculate_firewall_rule_form() {
  var $all_or_components = jQuery('.firewall_rule_repeater .ar-firewall-or');
  var firewall_rule = [];

  $all_or_components.each(function(index, or_div) {
    var or_div = calculate_firewall_rule_or( or_div );
    if (or_div) {
      firewall_rule.push(or_div);
    }
  });

  if (firewall_rule.length == 0) {
    return false;
  }

  return firewall_rule;
}
function update_rule_export_filter() {
  var rule_filter_arr = calculate_firewall_rule_form();
  
  if (!rule_filter_arr || rule_filter_arr.length <= 0) {
    return false
  }
  
  let rule_filter_str = convert_filter_to_filter_array(rule_filter_arr)

  jQuery('#export_filter').val(rule_filter_str)
}


function reset_firewall_rule_remove_buttons () {
  var remove_parents = jQuery('.filter_value')
  if (remove_parents.length > 1) {
    remove_parents.each(function(index, filter_value) {
      if (jQuery(filter_value).children('.firewall_rule_row--delete').length == 1) return;
      jQuery(filter_value).append('<div class="firewall_rule_row--delete"><button data-action="delete"><svg width="14" aria-hidden="true" focusable="false" data-prefix="far" data-icon="trash-alt" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="svg-inline--fa fa-trash-alt fa-w-14 c-cdnFirewallVetitiRules__trashIcon"><path fill="currentColor" d="M268 416h24a12 12 0 0 0 12-12V188a12 12 0 0 0-12-12h-24a12 12 0 0 0-12 12v216a12 12 0 0 0 12 12zM432 80h-82.41l-34-56.7A48 48 0 0 0 274.41 0H173.59a48 48 0 0 0-41.16 23.3L98.41 80H16A16 16 0 0 0 0 96v16a16 16 0 0 0 16 16h16v336a48 48 0 0 0 48 48h288a48 48 0 0 0 48-48V128h16a16 16 0 0 0 16-16V96a16 16 0 0 0-16-16zM171.84 50.91A6 6 0 0 1 177 48h94a6 6 0 0 1 5.15 2.91L293.61 80H154.39zM368 464H80V128h288zm-212-48h24a12 12 0 0 0 12-12V188a12 12 0 0 0-12-12h-24a12 12 0 0 0-12 12v216a12 12 0 0 0 12 12z"></path></svg></button></div>')
    });
  } else {
    jQuery('.firewall_rule_row--delete').remove();
  }

  jQuery('.ar-firewall-or').each(function(index, or_div) {
    if (jQuery(or_div).children('.ar-firewall-and').length < 1) {
      jQuery(or_div).prev('.c-cdnFirewallRules__orChipsContainer').remove()
      jQuery(or_div).remove()
    }
  })


  update_rule_export_filter();
}
