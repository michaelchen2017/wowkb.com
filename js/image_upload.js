

  function handleFileSelect(evt) {
    var files = evt.target.files; // FileList object
  //  document.getElementById('list').innerHTML = "";
    // Loop through the FileList and render image files as thumbnails.
    for (var i = 0; i<files.length; i++) {
    	  var f = files[i];
      // Only process image files.
      if (!f.type.match('image.*')) {
        alert("只有图片类型的文件会被上传。");
        //files.splice(i, 1);
        continue;
      }
      /*
      var reader = new FileReader();

      // Closure to capture the file information.
      reader.onload = (function(theFile) {
        return function(e) {
          // Render thumbnail.
          var span = document.createElement('span');
          span.innerHTML = ['<img class="thumb" src="', e.target.result,
                            '" title="', escape(theFile.name), '"/>'].join('');
          document.getElementById('list').insertBefore(span, null);
        };
      })(f);

      // Read in the image file as a data URL.
      reader.readAsDataURL(f);
      */
    }
  }

 