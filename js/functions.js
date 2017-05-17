require("owl.carousel");

$('body').on('click', '.dropdown.open .dropdown-menu', function(e){
	e.stopPropagation();
});

$('.dropdown').on('shown.bs.dropdown', function (e) {
	if($(this).attr('data-animation')) {
	$animArray = [];
	$animation = $(this).data('animation');
	$animArray = $animation.split(',');
	$animationIn = 'animated '+$animArray[0];
	$animationOut = 'animated '+ $animArray[1];
	$animationDuration = ''
	if(!$animArray[2]) {
		$animationDuration = 500; //if duration is not defined, default is set to 500ms
	}
	else {
		$animationDuration = $animArray[2];
	}
	
	$(this).find('.dropdown-menu').removeClass($animationOut)
	$(this).find('.dropdown-menu').addClass($animationIn);
	}
});

$('.dropdown').on('hide.bs.dropdown', function (e) {
	if($(this).attr('data-animation')) {
		e.preventDefault();
		$this = $(this);
		$dropdownMenu = $this.find('.dropdown-menu');
	
		$dropdownMenu.addClass($animationOut);
		setTimeout(function(){
			$this.removeClass('open')
			
		}, $animationDuration);
	}
});

$('body').on('click', '.profile-menu > a', function(e){
	e.preventDefault();
	$(this).parent().toggleClass('toggled');
	$(this).next().slideToggle(200);
});

$('body').on('click', '.a-prevent', function(e){
	e.preventDefault();
});


// functions to change procedures text in lead process
$('#select-type').change(function(){

	var A = 'procédure convient lorsque l’ancienneté du chèque est supérieure à un an. Le chèque n’a plus valeur de titre '+
		'exécutoire, seule une procédure amiable peut déclencher le paiement de la part du débiteur.';
	var B = 'procédure convient lorsque l’ancienneté du chèque est inférieure à un an. .Le chèque a valeur de titre exécutoire.' +
		'Le but est d’obtenir le paiement de régularisation par une saisie bancaire sur les comptes de l’émetteur du chèque.';

	var type = this.value;
	if(type =='amiable'){
		$('#detail-procedure-ci').text(A);
	}
	else if(type =='pre-contentieux'){
		$('#detail-procedure-ci').text(B);
	}
});

$('#select-type-cc').change(function(){

	var A = 'Recouvrer la créance par une négociation amiable avec le débiteur, soit un paiement complet ou mise en plca d’un  échéancier';
	var B = 'procédure recommandée pour les clients récalcitrants qui ont déjà été relancé sans succès. Cette procèdure est  préconisée si votre débiteur est un particulier ou une TPE/PMI';
	var C = 'procédure recommandée pour les clients « résistants », et pour lesquels une résolution amiable a été engagée sans   succès. L’objectif est d’obtenir de la part du juge un titre exécutoire et le faire appliquer.';
	var D = 'procédure recommandée lorsque le créancier et le débiteur sont tous deux des sociétés commerciales. La créance est récente (moins d’un an). Le créancier doit disposer du bon de commande ou devis + la preuve de la réalisation de la prestation ou de    la livraison du produit. C’est une procédure de saisie du tribunal en urgence.';

	var type = this.value;
	if(type =='amiable'){
		$('#detail-procedure-cc').text(A);
	}
	else if(type =='pre-contentieux'){
		$('#detail-procedure-cc').text(B);
	}
	else if(type =='injonction-de-payer'){
		$('#detail-procedure-cc').text(C);
	}
	else if(type =='assignation-en-refere'){
		$('#detail-procedure-cc').text(D);
	}
});

$('#select-type-li').change(function(){

	var A = 'procédure convient lorsque votre locataire a entre 1 et 3 mois de retard de paiement. L’objectif de cette procédure  est d’obtenir par une négociation amiable le paiement. A défaut, une procédure précontentieuse est ouverte.';
	var B = 'procédure convient lorsque toute tentative de conciliation amiable avec votre locataire n’a pas abouti. L’objectif est  d’obtenir un titre exécutoire et procéder à une saisie sur les comptes bancaires du locataire.';
	var C = 'L’objectif est d’obtenir l’expulsion de votre locataire ainsi que le paiement des loyers dus';

	var type = this.value;
	if(type =='pre-contentieux'){
		$('#detail-procedure-li').text(A);
	}
	else if(type =='assignation-saisie'){
		$('#detail-procedure-li').text(B);
	}
	else if(type =='assignation-expulsion'){
		$('#detail-procedure-li').text(C);
	}
})

$('#select-type-rc').change(function(){

	var A = 'Recouvrer la créance par une négociation amiable avec le débiteur, soit un paiement complet ou mise en place d’un échéancier';
	var B = 'procédure recommandée pour les clients récalcitrants qui ont déjà été relancé sans succès. Cette procédure est  préconisée si votre débiteur est un particulier ou une TPE/PMI';
	var C = 'procédure recommandée pour les clients « résistants », et pour lesquels une résolution amiable a été engagée sans succès. L’objectif est d’obtenir de la part du juge un titre exécutoire et le faire appliquer.';
	var D ='Recommandation : procédure recommandée lorsque le créancier et le débiteur sont tous deux des sociétés commerciales. La créance  est ancienne (plus d’un an). Le créancier doit disposer du bon de commande ou devis + la preuve de la réalisation de la prestation ou de la livraison du produit. C’est une procédure contradictoire.';

	var type = this.value;
	if(type =='amiable'){
		$('#detail-procedure-rc').text(A);
	}
	else if(type =='pre-contentieux'){
		$('#detail-procedure-rc').text(B);
	}
	else if(type =='injonction-de-payer'){
		$('#detail-procedure-rc').text(C);
	}
	else if(type =='assignation-au-fond'){
		$('#detail-procedure-rc').text(D);
	}
})

if($("#light-gallery").length){

	$("#light-gallery").owlCarousel({
		autoplay: true,
		autoplayTimeout: 3000,
		mouseDrag: false,
		nav: true,
		navText: ['<span class="fa fa-angle-left tab-next-prev"></span>', '<span class="fa fa-angle-right tab-next-prev"></span>'],
		items: 1,
		loop: true,
		animateIn: true,
		responsiveClass: true,
		responsive: {
			0: {
				items: 1,

			},
			768: {
				items: 3,
			},
			768: {
				items: 3,
			},
			1024: {
				items: 4,
			},
			1200: {
				items: 5,
			}
		}
	});

}
