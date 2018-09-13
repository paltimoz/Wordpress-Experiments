jQuery(document).ready(function() {

	var meta_image_frame;
    var current_field_id;
    var container_id, src_class;

    // Runs when the image button is clicked.
    jQuery('.btn-upload-img').click(function(e){
        // Prevents the default action from occuring.
        e.preventDefault();

        // Get the id of the corresponding upload field.
        current_field_id = $( this ).data( "field-id" );
            container_id = "#" + current_field_id  + "_container";
            src_class = "#" + current_field_id + "_src";

            // If the frame already exists, re-open it.
            if ( meta_image_frame ) {
                    meta_image_frame.open();
                    return;
            }

            // Sets up the media library frame
            meta_image_frame = wp.media.frames.meta_image_frame = wp.media({
                    title: 'Choose Image',
                    button: { text:  'Choose Image' },
                    library: { type: 'image' },
            });

            // Runs when an image is selected.
            meta_image_frame.on('select', function(){

                // Grabs the attachment selection and creates a JSON representation of the model.
                var media_attachment = meta_image_frame.state().get('selection').first().toJSON();

                // Sends the attachment URL to our custom image input field.
                jQuery('#' + current_field_id).val(media_attachment.url);
                jQuery(container_id).append('<span class="pz_img_close" data-field-id="' + current_field_id + '"></span>');
                jQuery(container_id).removeClass( "hidden" );
                // If the image is smaller than the thumbnail size, use the main URL
                if (typeof media_attachment.sizes.thumbnail === 'undefined') {
                    jQuery(src_class).attr('src', media_attachment.url);	
                } else {
                    jQuery(src_class).attr('src', media_attachment.sizes.thumbnail.url);	
                }
            });

            // Opens the media library frame.
            meta_image_frame.open();
        });

        var meta_gallery_frame;
        // Runs when the image button is clicked.
        jQuery('.btn-upload-gallery').click(function(e){

                //Attachment.sizes.thumbnail.url/ Prevents the default action from occuring.
                e.preventDefault();

                current_field_id = $( this ).data( "field-id" );

                // If the frame already exists, re-open it.
                if ( meta_gallery_frame ) {
                        meta_gallery_frame.open();
                        return;
                }

                // Sets up the media library frame
                meta_gallery_frame = wp.media.frames.meta_gallery_frame = wp.media({
                    title: 'Choose Images',
                    button: { text:  'Choose Images' },
                    library: { type: 'image' },
			        multiple: true
                });

		meta_gallery_frame.on('open', function() {
			var selection = meta_gallery_frame.state().get('selection');
			var library = meta_gallery_frame.state('gallery-edit').get('library');
			var ids = jQuery('#' + current_field_id).val();
			if (ids) {
				idsArray = ids.split(',');
				idsArray.forEach(function(id) {
					attachment = wp.media.attachment(id);
					attachment.fetch();
					selection.add( attachment ? [ attachment ] : [] );
				});
			}
		});

		meta_gallery_frame.on('ready', function() {
			jQuery( '.media-modal' ).addClass( 'no-sidebar' );
			//fixBackButton();
		});
		 
		// When an image is selected, run a callback.
		//meta_gallery_frame.on('update', function() {
		meta_gallery_frame.on('select', function() {
			var imageIDArray = [];
			var imageHTML = '';
			var metadataString = '';
			images = meta_gallery_frame.state().get('selection');
			imageHTML += '<ul class="pz_gallery_list">';
			images.each(function(attachment) {
                console.debug(attachment.attributes);
				imageIDArray.push(attachment.attributes.id);
                if (typeof attachment.attributes.sizes.thumbnail === 'undefined') {
                    imageHTML += '<li><div class="pz_gallery_container"><span class="pz_gallery_close" data-field-id="' + current_field_id + '"><img id="'+attachment.attributes.id+'" src="'+attachment.attributes.url+'"></span></div></li>';
                } else {
					imageHTML += '<li><div class="pz_gallery_container"><span class="pz_gallery_close" data-field-id="' + current_field_id + '"><img id="'+attachment.attributes.id+'" src="'+attachment.attributes.sizes.thumbnail.url+'"></span></div></li>';
				}
			});
			imageHTML += '</ul>';
			metadataString = imageIDArray.join(",");
			if (metadataString) {
				jQuery("#"+current_field_id).val(metadataString);
				jQuery("#pz_gallery_src").html(imageHTML);
				setTimeout(function(){
					ajaxUpdateTempMetaData();
				},0);
			}
		});
		 
		// Finally, open the modal
		meta_gallery_frame.open();
		
    });

        
    jQuery('.btn-remove-img').click(function(e){
        event.preventDefault();

        // Get the id of the corresponding upload field.
        current_field_id = $( this ).data( "field-id" );
        container_id = "." + current_field_id  + "_container";

        if (confirm('Are you sure you want to remove this image?')) {
            jQuery(container_id).remove();
            jQuery("#" + current_field_id).val('');
            $( this ).addClass( "hidden" );
		}
    });
    
	jQuery('.btn-remove-gallery').click(function(e){

        event.preventDefault();
        
        // Get the id of the corresponding upload field.
        current_field_id = $( this ).data( "field-id" );

		if (confirm('Are you sure you want to remove this image?')) {

			var removedImage = jQuery(this).children('img').attr('id');
			var oldGallery = jQuery("#" + current_field_id).val();
			var newGallery = oldGallery.replace(','+removedImage,'').replace(removedImage+',','').replace(removedImage,'');
			jQuery(this).parents().eq(1).remove();
			jQuery("#" + current_field_id).val(newGallery);
		}

    });
    
    jQuery(document.body).on('click', '.pz_img_close', function(event){
        event.preventDefault();

        current_field_id = $( this ).data( "field-id" );
        container_id = "#" + current_field_id  + "_container";

        if (confirm('Are you sure you want to remove this image?')) {
                jQuery(container_id).remove();
                jQuery("#" + current_field_id).val('');
}
    });

    jQuery(document.body).on('click', '.pz_gallery_close', function(event){

    event.preventDefault();

    current_field_id = $( this ).data( "field-id" );

    if (confirm('Are you sure you want to remove this image?')) {

        var removedImage = jQuery(this).children('img').attr('id');
        var oldGallery = jQuery("#" + current_field_id).val();
        var newGallery = oldGallery.replace(','+removedImage,'').replace(removedImage+',','').replace(removedImage,'');
        jQuery(this).parents().eq(1).remove();
        jQuery("#" + current_field_id).val(newGallery);
    }

    });


});