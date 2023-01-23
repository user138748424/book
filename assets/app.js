/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

// start the Stimulus application
import './bootstrap';
import './scripts/pjax';


$(document).ready(function(){
  let options = {
     scrollTo: false,
     push: false,
    // fragment: '#books_list',
    timeout: 2000
  }

  $(document).pjax('a', '#pjax-container', options);

  $('.rating-plus').find('.btn').each(function(ind, el){
    el.addEventListener('click', changeRating);
  });

  calculateRating();
})

function changeRating() {
  let oldValue;
  let likes = $('.likes');
  let dislikes = $('.dislikes');

  if ($(this).hasClass('likes-btn')) {
    oldValue = $(likes).val();
    $(likes).val(++oldValue);
  } else {
    oldValue = $(dislikes).val();
    $(dislikes).val(++oldValue);
  }

  calculateRating();
}

function calculateRating()
{
  let likesValue = Number($('.likes').val());
  let disLikesValue = Number($('.dislikes').val());
  let difference = likesValue - disLikesValue;
  difference = (difference >= 0) ? difference : 0;
  let sum = likesValue + disLikesValue;
  let rating = toFixed((difference / sum) * 10, 4);
  rating = isNaN(rating) ? 0 : rating;
  $('.rating').val(rating);
}

function toFixed(value, precision) {
  var power = Math.pow(10, precision || 0);
  return String(Math.round(value * power) / power);
}