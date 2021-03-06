var Router = function (lang) {

	// We do not redirect to the same URL, depending on the language
	switch (lang) {
		case 'fr' :
			this.base_url = 'http://app.wisembly.com/';
		break;
		case 'en' :
			this.base_url = 'http://app.wisembly.com/' ;
		break;
	}

	this.routes = {
		'event_create' : 'wiz/create'
	}

	this.get = function (route) {
		return this.base_url + this.routes[route];
	}
}