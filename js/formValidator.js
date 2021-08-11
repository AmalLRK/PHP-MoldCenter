let formValidator = function(btnName) {
		this.DOMElements = {
			btn: null
			, inputs: []
			, selects: []
		};
		this.options= {};
		
		this.elmName = btnName;

		if (document.querySelector('[data-formName="' + btnName + '"]') !== null)
		{
			this.DOMElements.btn = document.querySelector('[data-formName="' + btnName + '"]');
			this.DOMElements.btn.addEventListener('click', () => {
				this.checkDOMElements();
			});
		}

		this.addElement = function (formElement, type = 'input') {
			switch(type)
			{
				case 'input':
					this.DOMElements.inputs.push(formElement);
					break;
				case 'select':
					this.DOMElements.selects.push(formElement);
					break;
			}
		};
		this.searchElement = function () {
			document.querySelectorAll('input[data-form="' + this.elmName + '"]').forEach((elm) => {
				this.addElement(elm);
			});

			document.querySelectorAll('select[data-form="' + this.elmName + '"]').forEach((elm) => {
				this.addElement(elm, 'select');
			});

			document.querySelectorAll('textarea[data-form="' + this.elmName + '"]').forEach((elm) => {
				this.addElement(elm);
			});
		};

		this.checkDOMElements =  function() {
			let promise = new Promise((resolve, reject) => {
				if (this.DOMElements.inputs.length > 0)
				{
					this.DOMElements.inputs.forEach((elm, idx) => {
						if (elm.value === '')
						{

							elm.setAttribute('required', 'required');
							elm.parentNode.classList.add('is-invalid');
							reject({selects: elm});
						}
						
						if (this.DOMElements.inputs.length == idx + 1)
						{
							resolve({inputs: true});
						}
					});	
				}
				
				if (this.DOMElements.selects.length > 0)
				{
					this.DOMElements.selects.forEach((elm, idx) => {

						if (elm.value === '0' || elm.value === '')
						{
							elm.setAttribute('required', 'required');
							elm.parentNode.classList.add('is-invalid');
							reject({selects: elm});
						}

						if (this.DOMElements.selects.length == idx + 1)
						{
							resolve({selects: true});
						}
					});
				}

				if (this.DOMElements.selects.length === 0 && this.DOMElements.inputs.length === 0)
					resolve({inputs: true});
			});

			return promise
		};
		this.searchElement();
	}