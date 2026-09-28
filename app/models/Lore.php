<?php
namespace App\Models;

/**
 * Narrative content for The Curing Process - the story world behind
 * Hue U Xchange. Kept in a model (not hard-coded in the view) so the
 * view only presents data, matching the rest of the MVC structure.
 * Content is adapted from "Donny D: The Curing Process" by Donavan
 * McFadden.
 */
class Lore
{
    public static function artist()
    {
        return array(
            'name' => 'Donny D',
            'bio'  => array(
                'Donny D is a young Cosmic Creator and scholarship student at Hue University, "where brilliant young minds come to unlock the light intrinsic to their nature." He is the only creator selected in twenty-five years - the last was his father, who was lost to the Void.',
                'Gifted but distracted, Donny drifted toward the astro-lotus, late nights, and missed lessons. His Sage, Nivlema, exiled him fifty-five feet deep in an enchanted chasm on Yoo-U, a small celestial planet hidden from most maps, with no magic, no tablet, and no wings until he could free himself.',
                'What followed is The Curing Process: facing the illusions of fear, earning the respect of the camp with his freestyles, and returning to the observatory as an official Lightbearer, ready to create.',
            ),
        );
    }

    /** Key figures from the story, in the order they appear. */
    public static function characters()
    {
        return array(
            array('name' => 'Nivlema', 'role' => 'The Sage', 'text' => 'Keeper of the crystal observatory in the heart of the sun. Stern, emerald-eyed, and devoted to raising Donny "from a smoldering ember into a fervent flame."'),
            array('name' => 'Donny D', 'role' => 'The Cosmic Creator', 'text' => 'The pharaoh-crowned student whose exile on Yoo-U becomes his initiation into the light.'),
            array('name' => 'Sophyst, Leo & Swaglord', 'role' => 'The Crew', 'text' => 'Companions on the road - rhythm, astro-lotus smoke, and hard lessons about who you spend your time with.'),
            array('name' => 'Hope', 'role' => 'Hopeful Camp', 'text' => 'A painter and singer whose riddles point the way out of the dark.'),
            array('name' => 'The Void', 'role' => 'The Warning', 'text' => 'Where the brightest stars end up when they are left to implode on themselves. Only a glimmer of their light remains.'),
        );
    }

    /** The oath every Lightbearer takes - quoted from Nivlema's letter. */
    public static function oath()
    {
        return array('Live authentically', 'Apply selfless service to your actions', 'Seek harmony in all aspects of life and creation');
    }

    /** Songs that start in the story (marked with * in the screenplay). */
    public static function tracks()
    {
        return array(
            array('title' => 'What Do I Got to Fear?', 'moment' => 'Chapter 2 - Donny and Swaglord at Hue University'),
            array('title' => 'Proud of You', 'moment' => 'The tune Donny wrote in music class'),
            array('title' => 'Just Like Him', 'moment' => 'Donny wrestles with his father\'s fate'),
            array('title' => 'Crazy In My Bedroom', 'moment' => 'Tears in the chasm on Yoo-U'),
            array('title' => 'Light Bearers', 'moment' => 'The freestyle that earns the camp\'s respect'),
        );
    }

    /** Planned story and catalog releases that are not available yet. */
    public static function comingSoon()
    {
        return array(
            array('title' => 'Book 2', 'text' => 'Donny returns to Hue University to finish school - and to keep himself out of the Void.'),
            array('title' => 'Full Music EP Release', 'text' => 'The complete Lightbearer soundtrack, track by track.'),
            array('title' => 'Hopeful Camp Mural Series', 'text' => 'Art prints inspired by Hope\'s wall at the camp.'),
        );
    }
}
