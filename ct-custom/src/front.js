import './styles/main.scss';
import Navigation from './scripts/navigation';

const nav = document.querySelector( '[data-main-nav]' );
if ( nav ) {
	new Navigation( nav ).init();
}
