// console.log(htcc_var);
// console.log(htcc_values);
// console.log(htcc_m);
// console.log(wp_chatbot_log);


var screen_width = screen.width;
// ---  console.log('screen width: ' + screen_width); 
var mobile_screen_width = htcc_var.mobile_screen_width;


var device_based_on = htcc_var.detect_device;
var php_is_mobile = htcc_var.php_is_mobile;
var is_mobile = 'yes';

// device based on : php ? screen_width
// is_mobile - is mobile : yes ? no
if (device_based_on == 'php') {
    is_mobile = php_is_mobile;
} else {
    if (screen_width > mobile_screen_width) {
        is_mobile = 'no'; 
    } 
}

// ---  console.log('is_mobile: ' + is_mobile); 




if ( "yes" == is_mobile ) {
    if( 'yes' == htcc_m.cc_enable_mobile ) {
        change_position();
    }
} else {
    if( 'yes' == htcc_m.cc_enable ) {
        change_position();
    }
}



function change_position() {
    
    if( 'yes' == is_mobile ) {
        // mobile positions
        var cc_i_p1 = htcc_m.cc_i_m_p1;
        var cc_i_p1_value = htcc_m.cc_i_m_p1_value;

        var cc_i_p2 = htcc_m.cc_i_m_p2;
        var cc_i_p2_value = htcc_m.cc_i_m_p2_value;

        var cc_g_p1 = htcc_m.cc_g_m_p1;
        var cc_g_p1_value = htcc_m.cc_g_m_p1_value;

        var cc_g_p2 = htcc_m.cc_g_m_p2;
        var cc_g_p2_value = htcc_m.cc_g_m_p2_value;
    } else {
        var cc_i_p1 = htcc_m.cc_i_p1;
        var cc_i_p1_value = htcc_m.cc_i_p1_value;

        var cc_i_p2 = htcc_m.cc_i_p2;
        var cc_i_p2_value = htcc_m.cc_i_p2_value;

        var cc_g_p1 = htcc_m.cc_g_p1;
        var cc_g_p1_value = htcc_m.cc_g_p1_value;

        var cc_g_p2 = htcc_m.cc_g_p2;
        var cc_g_p2_value = htcc_m.cc_g_p2_value;
    }
    


    var sheet = document.createElement('style')
    sheet.innerHTML = ".fb_dialog { "+cc_i_p1+": "+cc_i_p1_value+" !important; "+cc_i_p2+": "+cc_i_p2_value+" !important; }";
    sheet.innerHTML += ".fb-customerchat iframe { "+cc_g_p1+": "+cc_g_p1_value+" !important; "+cc_g_p2+": "+cc_g_p2_value+" !important; }";
    // ---  console.log(sheet); 
    document.body.appendChild(sheet);

}












jQuery(document).ready(function($){


// check if fb is loaded
var checkfb = 1;

var is_callback_called = 'no';

function fbcheck() {

    if ( window.FB ) {
       // ---   console.log('FB Exists ..........'); 
        // parse_cc_code();
        start();
    } else {
       // ---   console.log('FB check' ); 
        checkfb++;
        if ( checkfb < 100 ) {
            setTimeout(fbcheck, 100);
        }
    }
}

// call fbcheck  - before that check fb sdk is added for this page..
if ( 'yes' == htcc_values.sdk_added ) {
   // ---   console.log('sdk added'); 
    fbcheck();   
} else {
   // ---   console.log('sdk not added'); 
}


/**
 * Show / Hide
 * 
 * after fb added then  this will run
 * 
 * Check Conditions - if all pass then check "when to load" messenger and  add cc code and parse 
 * 
 * Hide based on Device - Mobile, Desktop
 * Hide on Days in a Week
 * Hide on Time Range
 * 
 * 
 * 
 * add cc code  and check "when to load" messenger - and parse
 *     when to load  - here user can set based on device .. 
 *          - when page loads ( no-delay )
 *          - based on page scroll ( and/ or )
 *          - time delay
 * 
 *  - After loaded the messenger - call - Actions 
 */
function start() {

    // if not hide on current device - then check - days of the week - hide_on_days
    if ( 'not_hide' == htcc_hide_on_device() ) {

        // if not hided on days in week - then check - time range
        if ( 'not_hide' == htcc_hide_on_days() ) {

            // if not hided on time_range - then check - scrollY, time_delay
            //  and before then - display - woocommerce styles, shortcodes 
            if ( 'not_hide' == htcc_hide_time_range() ) {

                // Add Customer Chat Code
                add_cc_code();

                // add custom image based on conditions
                // if custom image is enabled - display ci
                // while parsing in any way - time, scroll, click - if ci exists - hide ci.
                // click event on ci to do_parse 
                custom_image();

            }

        }
    
    }

}






// Hide based on Time Range
function htcc_hide_time_range() {

   // ---   console.log('inside - htcc_hide_time_range'); 

    var hide_time_range = 'not_hide';


    var hide_time_start = htcc_var.hide_time_start;
    var hide_time_end = htcc_var.hide_time_end;

    // if hide time range values are not null .. 
    if ( '' !== hide_time_start && '' !== hide_time_end ) {

        var time = time_based_on_wordpress();

        var hide_time_start_array = hide_time_start.split(':');
        var hide_time_end_array = hide_time_end.split(':');

        var hide_time_start_hour = hide_time_start_array[0];
        var hide_time_start_minutes = hide_time_start_array[1];
        var hide_time_end_hour = hide_time_end_array[0];
        var hide_time_end_minutes = hide_time_end_array[1];


        var hour = time.getHours();
        var minutes = time.getMinutes();

        // if current time is after hide_time then yes
        // if current time is before the hide_time then yes
        // if both are yes - then hide the style.
        var after_start = 'no';
        var before_end = 'no';

        if ( hide_time_start_hour <= hour ) {
            after_start = 'yes';
            
            if ( hide_time_start_hour == hour && hide_time_start_minutes >= minutes ) {
                after_start = 'no';
            }
        }
        
        // e.g. start 18:00 , end 22:00 if current time is 20:00
        // 22:00 is greater then current time .. 
        var same_day = 'no'
        if ( hide_time_end_hour >= hour && hide_time_start_hour <= hour ) {
            same_day = "yes"
        }
        
        if ( hide_time_end_hour <= hour || "yes" == same_day ) {
            before_end = 'yes';

            if ( hide_time_end_hour == hour && hide_time_end_minutes <= minutes ) {
                before_end = 'no';
            }
        }

        if ( 'yes' == after_start && 'yes' == before_end ) {
            hide_time_range = 'hide';
        }
        
    }

   // ---   console.log('hide_time_range: ' + hide_time_range); 
    return hide_time_range;
}






// hide on device
function htcc_hide_on_device() {
    
   // ---   console.log('inside - htcc_hide_on_device '); 

    var hide_on_device = 'not_hide';

    if ( "yes" == is_mobile ) {
        if( 'yes' == htcc_var.hide_on_mobile ) {
            // hide on mobile
            hide_on_device = "hide";
        }
    } else {
        if( 'yes' == htcc_var.hide_on_desktop ) {
            // hide on desktop
            hide_on_device = "hide";
        }
    }

   // ---   console.log('hide_on_device: ' + hide_on_device ); 
    return hide_on_device;
}





/**
 * hide_on_days  - hide based on days in a week
 * 
 * @var current_time - current time, from where the website is accessed
 * @var time - is the time based on wordpress timezone
 * 
 */
function htcc_hide_on_days() {

   // ---   console.log('inside - hide_on_days'); 

    var htcc_var_hide_on_days = htcc_var.hide_on_days;

    var hide_on_days = "not_hide";

    if ( '' !== htcc_var_hide_on_days ) {

        hide_on_days = "hide";

        var time = time_based_on_wordpress();
       // ---   console.log(time); 
        var today_week = time.getDay();
       // ---   console.log('today week: ' + today_week); 
        // var days = htcc_var_hide_on_days.split(",");
        var days = JSON.parse("[" + htcc_var_hide_on_days + "]");
        
       // ---   console.log('Hide on this days: ' + days); 

        // if today_week is not in index of 'days'. then hide_on_days is not_hide
        // 0 greater then means its minus value - returns -1 for not in index 
        // if not in index - then it is not_hide
        if ( 0 > days.indexOf(today_week) ) {
            hide_on_days = "not_hide";
        }
    }

   // ---   console.log('hide_on_days: ' + hide_on_days ); 
    return hide_on_days;
}











// Convert local Time to time based on WordPress settings - time zone.
function time_based_on_wordpress() {

    var current_time = new Date();
    var current_time_ms = current_time.getTime();

    var time_offset = current_time.getTimezoneOffset() * 60000;

    var utc = current_time_ms + time_offset;
    var website_timezone = parseFloat(htcc_var.time_zone);
    var website_time = utc + 3600000 * website_timezone;

    // time based on WordPress timezone
    var wp_time = new Date(website_time);

    return wp_time;
}



// Add Customer chat code
function add_cc_code() {

   // ---   console.log('inside - add_cc_code '); 

    var cc_code = '<div id="htcc-customerchat" class="fb-customerchat" page_id="'+htcc_values.page_id+'"  theme_color="'+htcc_values.color+'" logged_in_greeting="'+htcc_values.greeting_login+'" logged_out_greeting="'+htcc_values.greeting_logout+'" ref="'+htcc_values.ref+'"  greeting_dialog_display="'+htcc_values.greeting_dialog_display+'"  greeting_dialog_delay="'+htcc_values.greeting_dialog_delay+'" ></div>';
   // ---   console.log(cc_code); 

    if (document.getElementById('htcc-messenger')) {
        $('#htcc-messenger').append(cc_code);
    }


    // dont break here - shortcode may need to parse ..

    parse_cc_code();

}





// parse based on device .. 
function parse_cc_code() {

   // ---   console.log('parse cc code - called'); 

    if ( 'yes' == is_mobile ) {
        parse_mobile();
    } else {
        parse_desktop();
    }


    
}


function parse_desktop() {

   // ---   console.log('parse_desktop'); 


   // parse based on time
   if ( '' !== htcc_var.parse_time ) {
        // ---  console.log('Parse after this ' + htcc_var.parse_time + ' seconds'); 
        setTimeout(function(){ 
            // ---  console.log('parsed... based on time ..'); 
            do_parse();
        }, htcc_var.parse_time * 1000 );
    }

    // parse based on scroll
    if ( '' !== htcc_var.parse_scroll ) {
        // ---  console.log('Parse after this ' + htcc_var.parse_scroll + ' scroll percent'); 
         window.addEventListener("scroll", event_parse_scroll, false);

         function event_parse_scroll(event) {

             var h = document.documentElement;
             var b = document.body;
             var st = 'scrollTop';
             var sh = 'scrollHeight';

             var percent = (h[st]||b[st]) / ((h[sh]||b[sh]) - h.clientHeight) * 100;

             // console.log(percent);

             if ( percent >= htcc_var.parse_scroll ) {
                 do_parse();
                 // console.log('parsed... based on scroll ..');
                 window.removeEventListener("scroll", event_parse_scroll );
             } 
         }
     }


    // if time, page scroll down percentage, custom image is not added then parse
    if ( '' == htcc_var.parse_time && '' == htcc_var.parse_scroll && 'no' == htcc_var.ci_enable ) {
        // console.log('parse directly - no-delay');
        do_parse();
    } 

}


function parse_mobile() {

   // ---   console.log('parse_mobile'); 

   // parse based on time
   if ( '' !== htcc_var.parse_time_mobile ) {
        // ---  console.log('Parse after this ' + htcc_var.parse_time_mobile + ' seconds'); 
        setTimeout(function(){ 
            // ---  console.log('parsed... based on time ..'); 
            do_parse();
        }, htcc_var.parse_time_mobile * 1000 );
    }


    // parse based on scroll
    if ( '' !== htcc_var.parse_scroll_mobile ) {
        // ---  console.log('Parse after this ' + htcc_var.parse_scroll_mobile + ' scroll percent'); 
        window.addEventListener("scroll", event_parse_scroll_mobile, false);

        function event_parse_scroll_mobile(event) {

            var h = document.documentElement;
            var b = document.body;
            var st = 'scrollTop';
            var sh = 'scrollHeight';

            var percent = (h[st]||b[st]) / ((h[sh]||b[sh]) - h.clientHeight) * 100;

            // console.log(percent);

            if ( percent >= htcc_var.parse_scroll_mobile ) {
                do_parse();
                // console.log('parsed... based on scroll ..');
                window.removeEventListener("scroll", event_parse_scroll_mobile );
            } 
        }
    }

    // if time, page scroll down percentage, custom image is not added then parse
    if ( '' == htcc_var.parse_scroll_mobile && '' == htcc_var.parse_scroll_mobile && 'no' == htcc_var.ci_enable_mobile ) {
        // console.log('parse directly - no-delay');
        do_parse();
    } 
         

}

// some times - fb may not ready to parse - getting errors
// so just added some time delay..
function do_parse() {
   // ---   console.log('do_parse'); 
    // parse();
    setTimeout(parse, 500);
}

// parse and call callbacks ..
function parse() {

   // ---   console.log('parse function '); 

    // FB.XFBML.parse();

    if (document.getElementById('htcc-messenger')) {
        FB.XFBML.parse(document.getElementById('htcc-messenger'), parsed_success );
       // ---   console.log('parsed'); 
    }

    // parse shortcode
    if (document.getElementById('htcc-messenger-shortcode')) {
        FB.XFBML.parse(document.getElementById('htcc-messenger-shortcode'), parsed_success );
       // ---   console.log('parsed shortcode'); 
    }

    // hide custom image - if exists .. 
    hide_ci();

    callbacks();

}

function parsed_success(){
   // ---   console.log('parsed_success'); 
}


// fb sdk is loaded and parsed
// call other function 

function callbacks() {

    // make this to run only one time .. 
    // console.log('is_callback_called: ' + is_callback_called);
    if ( 'no' == is_callback_called ) {
        click_events();
        pro_events();
        is_callback_called = 'yes';
        // console.log('is_callback_called: ' + is_callback_called);
    }

    
}






// show icon and dialog
function htcc_show() {
   // ---   console.log('htcc_show fn calls'); 
    FB.CustomerChat.show(true);
}

// show icon only
function htcc_show_icon() {
   // ---   console.log('htcc_show fn calls'); 
    FB.CustomerChat.show(false);
}

// Hide Entire plugin - Icon and Greetings 
function htcc_hide() {
   // ---   console.log('htcc_hide fn calls'); 
    FB.CustomerChat.hide();
}



// show dialog - if icon not shows then shows icon, dialog - similar to show(true)
function htcc_show_dialog() {
   // ---   console.log('htcc_show_dialog fn calls'); 
    FB.CustomerChat.showDialog();
}


// Hide dialog
function htcc_hide_dialog() {
   // ---   console.log('htcc_hide_dialog fn calls'); 
    FB.CustomerChat.hideDialog();
}


// Update Greetings Text, Ref
function update_greetings( lig, log, ref ) {
   // ---   console.log('update_greetings fn calls'); 

    FB.CustomerChat.update({  
        logged_in_greeting: lig,
        logged_out_greeting: log,  
        ref: ref
      });

}


/**
 * todo
 * 
 * 
 * class name: wp-chatbot-load
 * Click to Parse - load messenger when click on element contains class name - wp-chatbot-load
 * 
 * this parse wont check for hide on days, time range - when clicks after fb loaded - it loads messenger
 */
// function click_parse() {

//     // show icon and dialog
//     var click_load = document.querySelector('.wp-chatbot-load');

//     if (click_load) {
// ---  //         console.log('load messenger if clicked on element contains wp-chatbot-load class name');  
//         click_load.addEventListener('click', parse);
//     }

// }



/**
 * custom image
 */
function custom_image() {

    // ---  console.log('inside custom_image');

    if ( 'yes' == is_mobile ) {
        if ( 'no' == htcc_var.ci_enable_mobile ) {
            return;
        }
    } else {
        if ( 'no' == htcc_var.ci_enable ) {
            return;
        }
    }

    var ci = document.querySelector('.htcc-custom-image');

    // desktop values
    var p1 = htcc_var.p1;
    var p2 = htcc_var.p2;
    var p1_value = htcc_var.p1_value;
    var p2_value = htcc_var.p2_value;

    // mobile values 
    var m_p1 = htcc_var.m_p1;
    var m_p2 = htcc_var.m_p2;
    var m_p1_value = htcc_var.m_p1_value;
    var m_p2_value = htcc_var.m_p2_value;



    if ( ci ) {
        // ---  console.log('ci - adding styles .. ');
        // styles
        ci.style.position = "fixed";

        if ( 'yes' == is_mobile ) {
            // if mobile
            ci.style[m_p1] = m_p1_value;
            ci.style[m_p2]= m_p2_value;
        } else {
            ci.style[p1] = p1_value;
            ci.style[p2] = p2_value;
        }

        // display image
        ci.style.display = "block";

        // click event - parse
        // ci.addEventListener('click', do_parse);
        // ---  console.log('ready to parse if clicked on ci');
        ci.addEventListener('click', function(){
            parse();
        });
    }

}


function hide_ci() {
    // ---  console.log('hide ci'); 
    var ci = document.querySelector('.htcc-custom-image');

    if ( ci ) {
        // ci.style.display = "none";
        $(ci).fadeOut(1700);
    }
}



/**
 * Click Events
 */
function click_events() {



    // show icon and dialog
    var click_show = document.querySelector('.wp-chatbot-show');

    if (click_show) {
        click_show.addEventListener('click', htcc_show);
    }

    // show icon only
    var click_show_icon = document.querySelector('.wp-chatbot-show-icon');

    if (click_show_icon) {
        click_show_icon.addEventListener('click', htcc_show_icon);
    }


    // on click - update greetings 
    var click_update_greetings = document.querySelector('.wp-chatbot-greetings');

    if (click_update_greetings) {
        
        // click_update_greetings.addEventListener('click', update_greetings );
        click_update_greetings.addEventListener('click', function() {

        var click_lig = click_update_greetings.getAttribute('data-lig'); // logged in greetings
        var click_log = click_update_greetings.getAttribute('data-log'); // logged out greetings
        var click_ref = click_update_greetings.getAttribute('data-ref'); // ref

            FB.CustomerChat.update({  
                logged_in_greeting: click_lig,
                logged_out_greeting: click_log,  
                ref: click_ref
            });

            htcc_show();
        });

    }



    // Hide icon, Greetings
    var click_hide = document.querySelector('.wp-chatbot-fb-hide');

    if (click_hide) {
        click_hide.addEventListener('click', htcc_hide);
    }


}




/**
 * Events created from pro page
 */
function pro_events() {

    // Update Greetings
    var ug_enable = htcc_var.ug_enable;


    if ( 'yes' == ug_enable ) {


        var ug_lig = htcc_var.ug_lig;
        var ug_log = htcc_var.ug_log;
        var ug_ref = htcc_var.ug_ref;

        // placeholders
        var product_title = htcc_values.product;
        var ref_product = htcc_values.ref_product;
        var ref_title = htcc_values.ref_title;
        var ref_id = htcc_values.ref_id;

        ug_lig = ug_lig.replace('{{product}}', product_title);
        ug_log = ug_log.replace('{{product}}', product_title);

        // using global to replace all spaces .. 
        // for_ug_ref = product_title.replace(/ /g, '-' );
        ug_ref = ug_ref.replace('{{product}}', ref_product);
        ug_ref = ug_ref.replace('{{title}}', ref_title);
        ug_ref = ug_ref.replace('{{id}}', ref_id);

        var ug_time = htcc_var.ug_time;
        var ug_scroll = htcc_var.ug_scroll;

        // update greetings - time
        if ( '' !== ug_time ) {
           // ---   console.log('greeting dialog will update after ' + ug_time + ' seconds'); 
            setTimeout(function(){ 

                FB.CustomerChat.update({  
                    logged_in_greeting: ug_lig,
                    logged_out_greeting: ug_log,  
                    ref: ug_ref
                });

                htcc_show();

            }, htcc_var.ug_time * 1000 );

        }

        // update greetings - scroll
        if ( '' !== ug_scroll ) {

            window.addEventListener("scroll", event_ug_scroll, false);

            function event_ug_scroll(event) {


                var h = document.documentElement;
                var b = document.body;
                var st = 'scrollTop';
                var sh = 'scrollHeight';

                var percent = (h[st]||b[st]) / ((h[sh]||b[sh]) - h.clientHeight) * 100;

                // console.log(percent);

                // scroll position
                // var scroll_position = window.scrollY;
        
                if ( percent >= htcc_var.ug_scroll ) {

                    FB.CustomerChat.update({  
                        logged_in_greeting: ug_lig,
                        logged_out_greeting: ug_log,  
                        ref: ug_ref
                    });
                    
                    htcc_show();
                    window.removeEventListener("scroll", event_ug_scroll );
                } 
            }

        }


    } // Update Greetings  #END 


    // show_time - Show icon, Greetings
    if( '' !== htcc_var.show_time ) {
        // console.log('Show icon, Greetings after ' + htcc_var.show_time + ' seconds');
        setTimeout(function(){ 
            htcc_show();
        }, htcc_var.show_time * 1000 );
    }

    // show_icon_time - Show Icon only
    if( '' !== htcc_var.show_icon_time ) {
        // console.log('Show icon only after' + htcc_var.show_icon_time + ' seconds');
        setTimeout(function(){ 
            htcc_show_icon();
        }, htcc_var.show_icon_time * 1000 );
    }

    // gd_show_time - show greeting dialog
    if( '' !== htcc_var.gd_show_time ) {
        // console.log('show greeting dialog after' + htcc_var.gd_show_time + ' seconds');
        setTimeout(function(){ 
            htcc_show_dialog();
        }, htcc_var.gd_show_time * 1000 );
    }

    // gd_hide_time - hide greetings dialog
    if( '' !== htcc_var.gd_hide_time ) {
        // console.log('hide greetings dialog after' + htcc_var.gd_hide_time + ' seconds');
        setTimeout(function(){ 
            htcc_hide_dialog();
        }, htcc_var.gd_hide_time * 1000 );
    }

    // hide_time - hide icon, greeting dialog
    if( '' !== htcc_var.hide_time ) {
        // console.log('hide icon, greeting dialog after' + htcc_var.hide_time + ' seconds');
        setTimeout(function(){ 
            htcc_hide();
        }, htcc_var.hide_time * 1000 );
    }

    









}




// FB.XFBML.parse();
// FB.XFBML.parse(document.getElementById('foo'));
// FB.XFBML.parse(document.getElementById('htcc-messenger'));

// emitted when the Facebook JavaScript SDK has been initialized and the plugin is about to load.
// FB.Event.subscribe('customerchat.load', sdk_loaded());


});