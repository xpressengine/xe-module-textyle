function completeInsertTextyle(ret_obj, response_tags) {
	alert(ret_obj['message']);
	location.href=current_url.setQuery('act','dispTextyleAdminList');
}

function completeInsertGrant(ret_obj) {
	var error = ret_obj['error'];
	var message = ret_obj['message'];
	var page = ret_obj['page'];
	var module_srl = ret_obj['module_srl'];
	alert(message);
}

function completeInsertConfig(ret_obj, response_tags) {
	alert(ret_obj['message']);
	location.reload();
}

function completeDeleteTextyle(ret_obj) {
	alert(ret_obj['message']);
	location.href=current_url.setQuery('act','dispTextyleAdminList').setQuery('module_srl','');
}

function toggleAccessType(target) {
	switch(target) {
		case 'domain' :
				xGetElementById('textyleFo').domain.value = '';
				xGetElementById('accessDomain').style.display = 'block';
				xGetElementById('accessVid').style.display = 'none';
			break;
		case 'vid' :
				xGetElementById('textyleFo').vid.value = '';
				xGetElementById('accessDomain').style.display = 'none';
				xGetElementById('accessVid').style.display = 'block';
			break;
	}
}
