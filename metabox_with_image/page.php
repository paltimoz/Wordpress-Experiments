<?php

echo '<p>';
echo '<label>Custom Text</label><br>';
echo  get_custom_text(get_the_ID());
echo '</p>';

echo '<p>';
echo '<label>Single Image</label><br>';
echo  '<img src="' . get_single_image(get_the_ID()) . '" alt="Single Image" />';
echo '</p>';

echo '<p>';
echo '<label>Multiple Image</label><br>';
echo  get_multiple_image_with_html(get_the_ID());
echo '</p>';