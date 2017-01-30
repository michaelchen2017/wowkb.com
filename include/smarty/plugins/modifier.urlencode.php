<?php
function smarty_modifier_urlencode($string)
{
    return rawurlencode($string);
}