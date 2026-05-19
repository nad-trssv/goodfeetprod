(function (factory) {
    typeof define === 'function' && define.amd ? define(factory) : factory();
  })(function () {
    'use strict';
  
    // Инициализация TinyMCE через jQuery
    $('textarea.tinyarea').tinymce({
      height: 500,
      plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'fullscreen',
        'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
      ],
      toolbar: 'undo redo | blocks | bold italic backcolor | ' +
        'alignleft aligncenter alignright alignjustify | ' +
        'bullist numlist outdent indent | removeformat | help',
      statusbar: false,  // Отключаем статусбар
    });
  });
  