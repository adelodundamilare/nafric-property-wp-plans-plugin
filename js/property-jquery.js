jQuery("table.propertypayments td span.pay, table.eventspayments td span.pay").click(function (event) {
    var dateEvent = jQuery(this).attr('id');
    var this_status = jQuery('td.status span#status-' + dateEvent).html();
    jQuery("select#user-payment-status").val(this_status);
    jQuery.ajax({
        type: 'POST',
        url: ajax.url,
        data: {
            action: 'imic_property_function',
            id: dateEvent,
        },
        success: function (data) {
            jQuery("div#overlay #popup .msg ").empty();
            jQuery("div#overlay #popup .msg ").append(data);
            var overlay = document.getElementById("overlay");
            var popup = document.getElementById("popup");
            overlay.style.display = "block";
            popup.style.display = "block";
            jQuery("div#overlay .update-btn").attr('id', dateEvent);
            jQuery('.close-btn').click(function () {
                jQuery('.overlay-bg, .overlay-content').hide(); // hide the overlay
            });
        },
        error: function (errorThrown) {
        },
        complete: function () { }
    });
});
jQuery("div#overlay .update-btn").click(function (event) {
    var user_id = jQuery(this).attr('id');
    var userstatus = jQuery("#user-payment-status option:selected").text();
    // var manual_transaction_id = jQuery(this).closest('.overlay-content').find(".manual_transaction_id").val();
    var manual_transaction_id = 'bank-transfer-' + new Date().getTime();
    var plan_agent_email = jQuery(this).closest('.overlay-content').find(".plan_agent_email").attr('id');
    var plan_name = jQuery(this).closest('.overlay-content').find(".plan_name").attr('id');
    jQuery.ajax({
        type: 'POST',
        url: ajax.url,
        data: {
            action: 'imic_property_status_function',
            id: user_id,
            status: userstatus,
            manual_transaction_id: manual_transaction_id,
            plan_agent_email: plan_agent_email,
            plan_name: plan_name,
        },
        success: function (data) {
            var regex = /^[a-zA-Z ]*$/;
            if (data != null) {
                if (regex.test(data)) {
                    jQuery("table td.status span#status-" + user_id).empty();
                    jQuery("table td.status span#status-" + user_id).html(data);
                }
                else {
                    jQuery("table td.transaction_id span#transaction-" + user_id).empty();
                    jQuery("table td.transaction_id span#transaction-" + user_id).html(data);
                    jQuery("table td.status span#status-" + user_id).empty();
                    jQuery("table td.status span#status-" + user_id).html('Completed');
                }
            }
            jQuery('.overlay-bg, .overlay-content').hide(); // hide the overlay   
            //			
        },
        error: function (errorThrown) {
        }
    });
});