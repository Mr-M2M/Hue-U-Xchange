<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Lore;

/** The Curing Process - artist biography, story, music, and Coming Soon. */
class LoreController extends Controller
{
    public function index()
    {
        $artist = Lore::artist();
        $this->render('lore/index', array(
            'pageTitle'  => 'The Curing Process - Hue U Xchange',
            'artist'     => $artist,
            'characters' => Lore::characters(),
            'oath'       => Lore::oath(),
            'tracks'     => Lore::tracks(),
            'comingSoon' => Lore::comingSoon(),
        ));
    }
}
