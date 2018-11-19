function make_param_list(ar_list) {
	var l = new Object()
	for (var s in ar_list) {
		s = ar_list[s]
		//alert(s)
		l[s] = $('#'+s).val()
	}
	return l
}

function copy_to_clipboard(what) {
    $("#ready_for_clipboard").val("")
    $(what).each(function(){
        //console.log($(this).text())
        $("#ready_for_clipboard").val($("#ready_for_clipboard").val().trim() + $(this).text())
    })
    $("#ready_for_clipboard").select()
    document.execCommand("copy")
}

function get_commented_string(data) {
    d = data.indexOf("-->")
    d = data.substring(0,d).replace("<!--","")
    return d
}

function selectElementContents(el) {
    el = document.getElementById(el)
    var body = document.body, range, sel;
    if (document.createRange && window.getSelection) {
    	console.log('Caso 1')
        range = document.createRange();
        sel = window.getSelection();
        sel.removeAllRanges();
        try {
            range.selectNodeContents(el);
            sel.addRange(range);
        } catch (e) {
            range.selectNode(el);
            sel.addRange(range);
        }
        document.execCommand("Copy");
        sel.removeAllRanges();
    } else if (body.createTextRange) {
    	console.log('Caso 2')
    	range = body.createTextRange();
        range.moveToElementText(el);
        range.select();
        range.execCommand("Copy");
    }
}

function Nav(where) {
    window.location.href = where
}

