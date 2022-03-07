
<link rel="stylesheet" type="text/css"  href="screen_layout_large.css" />
<link rel="stylesheet" type="text/css" media="only screen and (min-width:50px) and (max-width:600px)"    href="screen_layout_small.css">
<link rel="stylesheet" type="text/css" media="only screen and (min-width:601px) and (max-width:800px)"   href="screen_layout_medium.css">

/* ARTICLE PADDING.  START THE FIRST LINE OF TEXT BELOW THE NAVIGATION */
@media only screen and (min-width:976px){
article { padding: 12em 0 0 0; }
}
@media only screen and (min-width:936px)  and (max-width:975px){
article { padding: 14em 0 0 0; }
}
@media only screen and (max-width:750px){
	article{padding:1em 0 0 0}
	header{display:none}article h1{display:none}.navrule1{display:none}
	.promo_container .promo.three {
		width:55%;
		margin: 0 auto;
		float: none;
		}

	nav {
		display: block;
		position: static;
		padding: 10px 0px 10px 10px;
		background-color: #515673;
		}
}
 
@media only screen and (min-width:810px){.promo.three{display:none}}
@media only screen and (max-width:810px){.promo.seven{display:none}}
@media screen and (orientation:portrait){.promo.three{display:none}}
@media screen and (orientation:landscape){.promo.six{display:none}}

.promo_container .promo.four img {width: 940px;}	
.promo_container .promo.five img {width: 940px;} 
.promo_container .promo.two img {
	width: 128px;
	display: block;
	margin: 0 auto;
	}

.promo_container .promo.seven img {
	width:473px; 
	margin: 0 auto;
	float: none;
	}	

.promo_container .promo.six img {
	padding-top:.25em;
	width:100%;
	margin: 0 auto;
	float: none;
	}
	
.promo_container .promo.three img {
	padding-top:.25em;
	width:100%;
	margin: 0 auto;
	float: none;
	}
	
.promo .one .content {	line-height: 1.45em;}