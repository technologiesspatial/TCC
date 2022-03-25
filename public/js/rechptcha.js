var correctCaptcha = function(response) {

  if (response.length == 0) {}
  else {
   $('#hiddenRecaptcha').val(response);
   $('#hiddenRecaptcha').removeClass('required');
   $('label[for="hiddenRecaptcha"]').remove();
  }
};