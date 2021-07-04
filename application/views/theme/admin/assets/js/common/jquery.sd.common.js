/**
 * jQuery common script 1.0
 *
 * http://solutionsdrive.com/
 *
 * Copyright (c) 2010 to ... Solutionsdrive
 *
 */

String.prototype.toSlug = function(replacement) {
    var o = {
        replacement: '-'
    };
    if (typeof replacement == 'string')
        o.replacement = replacement;
    return  this.toLowerCase().replace(/[^a-zA-Z0-9]/g, o.replacement).replace(/-+/g, o.replacement).replace(/-$/g, '');
}


function include(filename, filetype) {
    if (filetype == "js") { //if filename is a external JavaScript file
        var fileref = document.createElement('script')
        fileref.setAttribute("type", "text/javascript")
        fileref.setAttribute("src", filename)
    }
    else if (filetype == "css") { //if filename is an external CSS file
        var fileref = document.createElement("link")
        fileref.setAttribute("rel", "stylesheet")
        fileref.setAttribute("type", "text/css")
        fileref.setAttribute("href", filename)
    }
    if (typeof fileref != "undefined")
        document.getElementsByTagName("head")[0].appendChild(fileref)
}


if (typeof(jQuery) == "undefined") {
    include('http://code.jquery.com/jquery-1.9.1.js', 'js');
}

/*can use multiple seperate by coma
 *data-dismiss-attr="#NoticeFromDate:value,#dp4:data-date"
 **/
jQuery(document).ready(function() {
    $('[data-dismiss-attr]').on('click', function() {
        $.each(($(this).attr('data-dismiss-attr').split(',')), function(k, v) {
            var temp = v.split(':');
            $(temp[0]).attr(temp[1], '');
            if (temp[1] == 'value') {
                $(temp[0]).val('');
            }
        });
    });
});


/*[start] jquery equal to solution by data attr
 *  uses:
 <button data-equalto="#gender" type="button" value="Male" class="btn btn-info active">Male</button>
 <button data-equalto="#gender"  type="button" value="Female" class="btn btn-info">Female</button>
 **/
jQuery(document).ready(function() {
    $('[data-equalto]').each(function() {
        var $this = $(this);
        var tagName = $this.prop("tagName").toLowerCase(),
                tagType = $this.prop("type").toLowerCase(), tagValue = '';

        var events = '';
        if (tagName == 'button' || /button|reset/.test(tagType)) {
            events = 'click', tagValue = $this.val();
        }
        else if (/checkbox|radio/.test(tagType)) {
            events = 'change', tagValue = $this.val();
        }
        else if (/select/.test(tagName)) {
            events = 'change', tagValue = $this.val();
        }
        else if (/input|textarea|text/.test(tagName)) {
            events = 'change keyup', tagValue = $this.val();
        } else {
            events = 'change', tagValue = $this.html();
        }



//
//        console.log(selectors);
        $(this).on(events, function() {
            var selectors = $(this).attr('data-equalto').split(',');
            if (selectors) {
                $.each(selectors, function(sk, selector) {
                    toTagName = $(selector).prop("tagName").toLowerCase();

                    if (/input|button/.test(toTagName)) {
                        $(selector).val(tagValue);
                    } else {
                        $(selector).html(tagValue);
                    }
                    //if check box then checked
                    var toTagType = $(selector).prop("type");
                    if (/checkbox|radio/.test(toTagType) && /checkbox|radio/.test(tagType)) {
                        $(selector).attr('checked', $this.is(':checked'));
                    }

                });
            }
        });

        //change default select
        var defaultValue = $(this).attr('data-equalto_default');
        if (defaultValue) {
            if(tagValue==defaultValue){
                 $(this).addClass('active');
                 var trigger = events.split(' ');
                 if(trigger[0]){
                     $(this).trigger(trigger[0]);
                 }
//            console.log(defaultValue);
            }
        }
    });
});
/*[/end] jquery equal to solution by data attr*/