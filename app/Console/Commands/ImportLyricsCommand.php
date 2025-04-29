<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class ImportLyricsCommand extends Command
{
    protected $signature = 'import-lyrics';

    protected $description = 'Rip the song lyrics from azlyrics and save them to ';

    protected const BASE_URL = 'https://www.azlyrics.com/lyrics/weirdalyankovic/';

    private function importDirectory():string
    {
        $importDirectory = config('services.import_directory');

        if (blank($importDirectory)) {
            throw new \Exception('Import directory is not set in config/app.php');
        }

        // make sure that directory exists
        // defaults to storage/app/import
        if (!is_dir($importDirectory)) {
            mkdir($importDirectory);
            mkdir($importDirectory . '/lyrics');
        }

        if (!is_writable($importDirectory)) {
            throw new \Exception('Import directory is not writable: ' . $importDirectory);
        }

        return $importDirectory;
    }

    public function handle()
    {
        $importDirectory = $this->importDirectory();

        $this->songs()->each(function ($songs, $albumTitle) use ($importDirectory) {
            $this->info('Importing songs for album: ' . $albumTitle);

            collect($songs)->each(function ($song, $file) use ($importDirectory) {
                $url = self::BASE_URL . $file;

                $this->info('Importing song: ' . $song);

                $html = file_get_contents($url);

                $lyrics = $this->extractLyrics($html);

                file_put_contents($importDirectory . '/' . $file, $html);

                file_put_contents($importDirectory . '/lyrics/' . $file, $lyrics);

                sleep(rand(5,10));
            });
        });
    }


    private function songs(): Collection
    {
        return collect([
            'Weird Al Yankovic'                                        =>
                [
                    'ricky.html'                  => 'Ricky',
                    'gottaboogie.html'            => 'Gotta Boogie',
                    'iloverockyroad.html'         => 'I Love Rocky Road',
                    'buckinghamblues.html'        => 'Buckingham Blues',
                    'happybirthday.html'          => 'Happy Birthday',
                    'stopdragginmycararound.html' => 'Stop Draggin\' My Car Around',
                    'mybologna.html'              => 'My Bologna',
                    'thechecksinthemail.html'     => 'The Check\'s In The Mail',
                    'anotheroneridesthebus.html'  => 'Another One Rides The Bus',
                    'illbemellowwhenimdead.html'  => 'I\'ll Be Mellow When I\'m Dead',
                    'suchagroovyguy.html'         => 'Such A Groovy Guy',
                    'mrfrumpintheironlung.html'   => 'Mr. Frump In The Iron Lung',
                ],
            'Weird Al Yankovic In 3-D'                                 =>
                [
                    'eatit.html'                               => 'Eat It',
                    'midnightstar.html'                        => 'Midnight Star',
                    'thebradybunch.html'                       => 'The Brady Bunch',
                    'buymeacondo.html'                         => 'Buy Me A Condo',
                    'ilostonjeopardy.html'                     => 'I Lost On Jeopardy',
                    'polkason45.html'                          => 'Polkas On 45',
                    'mrpopeil.html'                            => 'Mr. Popeil',
                    'kingofsuede.html'                         => 'King Of Suede',
                    'thatboycoulddance.html'                   => 'That Boy Could Dance',
                    'themefromrockyxiiitheryeorthekaiser.html' => 'Theme From Rocky XIII (The Rye Or The Kaiser)',
                    'naturetrailtohell.html'                   => 'Nature Trail To Hell',
                ],
            'Dare To Be Stupid'                                        =>
                [
                    'likeasurgeon.html'                 => 'Like A Surgeon',
                    'daretobestupid.html'               => 'Dare To Be Stupid',
                    'iwantanewduck.html'                => 'I Want A New Duck',
                    'onemoreminute.html'                => 'One More Minute',
                    'yoda.html'                         => 'Yoda',
                    'georgeofthejungle.html'            => 'George Of The Jungle',
                    'slimecreaturesfromouterspace.html' => 'Slime Creatures From Outer Space',
                    'girlsjustwanttohavelunch.html'     => 'Girls Just Want To Have Lunch',
                    'thisisthelife.html'                => 'This Is The Life',
                    'cabletv.html'                      => 'Cable TV',
                    'hookedonpolkas.html'               => 'Hooked On Polkas',
                ],
            'Polka Party!'                                             =>
                [
                    'livingwithahernia.html'     => 'Living With A Hernia',
                    'dogeatdog.html'             => 'Dog Eat Dog',
                    'addictedtospuds.html'       => 'Addicted To Spuds',
                    'oneofthosedays.html'        => 'One Of Those Days',
                    'polkaparty.html'            => 'Polka Party!',
                    'heresjohnny.html'           => 'Here\'s Johnny',
                    'dontwearthoseshoes.html'    => 'Don\'t Wear Those Shoes',
                    'toothlesspeople.html'       => 'Toothless People',
                    'goodenoughfornow.html'      => 'Good Enough For Now',
                    'christmasatgroundzero.html' => 'Christmas At Ground Zero',
                ],
            'Even Worse'                                               =>
                [
                    'fat.html'                          => 'Fat',
                    'stuckinaclosetwithvannawhite.html' => 'Stuck In A Closet With Vanna White',
                    'thissongsjustsixwordslong.html'    => '(This Song\'s Just) Six Words Long',
                    'youmakeme.html'                    => 'You Make Me',
                    'ithinkimaclonenow.html'            => 'I Think I\'m A Clone Now',
                    'lasagna.html'                      => 'Lasagna',
                    'melanie.html'                      => 'Melanie',
                    'alimony.html'                      => 'Alimony',
                    'velvetelvis.html'                  => 'Velvet Elvis',
                    'twister.html'                      => 'Twister',
                    'goodolddays.html'                  => 'Good Old Days',
                ],
            'Peter And The Wolf'                                       =>
                [
                    'peterandthewolf.html'                         => 'Peter And The Wolf',
                    'carnivaloftheanimalsparttwointroduction.html' => 'Carnival Of The Animals Part Two, Introduction',
                    'aardvark.html'                                => 'Aardvark',
                    'hummingbirds.html'                            => 'Hummingbirds',
                    'snails.html'                                  => 'Snails',
                    'alligator.html'                               => 'Alligator',
                    'amoeba.html'                                  => 'Amoeba',
                    'pigeons.html'                                 => 'Pigeons',
                    'shark.html'                                   => 'Shark',
                    'cockroaches.html'                             => 'Cockroaches',
                    'iguana.html'                                  => 'Iguana',
                    'vulture.html'                                 => 'Vulture',
                    'unicorn.html'                                 => 'Unicorn',
                    'poodle.html'                                  => 'Poodle',
                    'finale.html'                                  => 'Finale',
                ],
            'UHF - Original Motion Picture Soundtrack And Other Stuff' =>
                [
                    'moneyfornothingbeverlyhillbillies.html'                 => 'Money For Nothing/Beverly Hillbillies',
                    'gandhiii.html'                                          => 'Gandhi II',
                    'attackoftheradioactivehamstersfromaplanetnearmars.html' => 'Attack Of The Radioactive Hamsters From A Planet Near Mars',
                    'islething.html'                                         => 'Isle Thing',
                    'thehotrockspolka.html'                                  => 'The Hot Rocks Polka',
                    'uhf.html'                                               => 'UHF',
                    'letmebeyourhog.html'                                    => 'Let Me Be Your Hog',
                    'shedriveslikecrazy.html'                                => 'She Drives Like Crazy',
                    'genericblues.html'                                      => 'Generic Blues',
                    'spatulacity.html'                                       => 'Spatula City',
                    'spam.html'                                              => 'Spam',
                    'thebiggestballoftwineinminnesota.html'                  => 'The Biggest Ball Of Twine In Minnesota',
                ],
            'Off The Deep End'                                         =>
                [
                    'smellslikenirvana.html'    => 'Smells Like Nirvana',
                    'triggerhappy.html'         => 'Trigger Happy',
                    'icantwatchthis.html'       => 'I Can\'t Watch This',
                    'polkayoureyesout.html'     => 'Polka Your Eyes Out',
                    'iwasonlykidding.html'      => 'I Was Only Kidding',
                    'thewhitestuff.html'        => 'The White Stuff',
                    'wheniwasyourage.html'      => 'When I Was Your Age',
                    'tacogrande.html'           => 'Taco Grande',
                    'airlineamy.html'           => 'Airline Amy',
                    'theplumbingsong.html'      => 'The Plumbing Song',
                    'youdontlovemeanymore.html' => 'You Don\'t Love Me Anymore',
                ],
            'Alapalooza'                                               =>
                [
                    'jurassicpark.html'              => 'Jurassic Park',
                    'youngdumbugly.html'             => 'Young, Dumb & Ugly',
                    'bedrockanthem.html'             => 'Bedrock Anthem',
                    'franks2000tv.html'              => 'Frank\'s 2000" TV',
                    'achybreakysong.html'            => 'Achy Breaky Song',
                    'trafficjam.html'                => 'Traffic Jam',
                    'talksoup.html'                  => 'Talk Soup',
                    'livininthefridge.html'          => 'Livin\' In The Fridge',
                    'shenevertoldmeshewasamime.html' => 'She Never Told Me She Was A Mime',
                    'harveythewonderhamster.html'    => 'Harvey The Wonder Hamster',
                    'waffleking.html'                => 'Waffle King',
                    'bohemianpolka.html'             => 'Bohemian Polka',
                ],
            'Bad Hair Day'                                             =>
                [
                    'amishparadise.html'            => 'Amish Paradise',
                    'everythingyouknowiswrong.html' => 'Everything You Know Is Wrong',
                    'cavitysearch.html'             => 'Cavity Search',
                    'callininsick.html'             => 'Callin\' In Sick',
                    'thealternativepolka.html'      => 'The Alternative Polka',
                    'sinceyouvebeengone.html'       => 'Since You\'ve Been Gone',
                    'gump.html'                     => 'Gump',
                    'imsosickofyou.html'            => 'I\'m So Sick Of You',
                    'syndicatedinc.html'            => 'Syndicated Inc.',
                    'irememberlarry.html'           => 'I Remember Larry',
                    'phonycalls.html'               => 'Phony Calls',
                    'thenightsantawentcrazy.html'   => 'The Night Santa Went Crazy',
                ],
            'Running With Scissors'                                    =>
                [
                    'thesagabegins.html'                => 'The Saga Begins',
                    'mybabysinlovewitheddievedder.html' => 'My Baby\'s In Love With Eddie Vedder',
                    'prettyflyforarabbi.html'           => 'Pretty Fly For A Rabbi',
                    'theweirdalshowtheme.html'          => 'The Weird Al Show Theme',
                    'jerryspringer.html'                => 'Jerry Springer',
                    'germs.html'                        => 'Germs',
                    'polkapower.html'                   => 'Polka Power!',
                    'yourhoroscopefortoday.html'        => 'Your Horoscope For Today',
                    'itsallaboutthepentiums.html'       => 'It\'s All About The Pentiums',
                    'truckdrivinsong.html'              => 'Truck Drivin\' Song',
                    'grapefruitdiet.html'               => 'Grapefruit Diet',
                    'albuquerque.html'                  => 'Albuquerque',
                ],
            'Poodle Hat'                                               =>
                [
                    'couchpotato.html'                 => 'Couch Potato',
                    'hardwarestore.html'               => 'Hardware Store',
                    'trashday.html'                    => 'Trash Day',
                    'partyatthelepercolony.html'       => 'Party At The Leper Colony',
                    'angrywhiteboypolka.html'          => 'Angry White Boy Polka',
                    'wannaburlovr.html'                => 'Wanna B Ur Lovr',
                    'acomplicatedsong.html'            => 'A Complicated Song',
                    'whydoesthisalwayshappentome.html' => 'Why Does This Always Happen To Me?',
                    'odetoasuperhero.html'             => 'Ode To A Superhero',
                    'bob.html'                         => 'Bob',
                    'ebay.html'                        => 'eBay',
                    'geniusinfrance.html'              => 'Genius In France',
                ],
            'Straight Outta Lynwood'                                   =>
                [
                    'whitenerdy.html'            => 'White & Nerdy',
                    'pancreas.html'              => 'Pancreas',
                    'canadianidiot.html'         => 'Canadian Idiot',
                    'illsueya.html'              => 'I\'ll Sue Ya',
                    'polkarama.html'             => 'Polkarama!',
                    'virusalert.html'            => 'Virus Alert',
                    'confessionspartiii.html'    => 'Confessions Part III',
                    'weaselstompingday.html'     => 'Weasel Stomping Day',
                    'closebutnocigar.html'       => 'Close But No Cigar',
                    'doicreepyouout.html'        => 'Do I Creep You Out',
                    'trappedinthedrivethru.html' => 'Trapped In The Drive-Thru',
                    'dontdownloadthissong.html'  => 'Don\'t Download This Song',
                ],
            'Alpocalypse'                                              =>
                [
                    'performthisway.html'             => 'Perform This Way',
                    'cnr.html'                        => 'CNR',
                    'tmz.html'                        => 'TMZ',
                    'skipperdan.html'                 => 'Skipper Dan',
                    'polkaface.html'                  => 'Polka Face',
                    'craigslist.html'                 => 'Craigslist',
                    'partyinthecia.html'              => 'Party In The CIA',
                    'ringtone.html'                   => 'Ringtone',
                    'anothertattoo.html'              => 'Another Tattoo',
                    'ifthatisntlove.html'             => 'If That Isn\'t Love',
                    'whateveryoulike.html'            => 'Whatever You Like',
                    'stopforwardingthatcraptome.html' => 'Stop Forwarding That Crap To Me',
                ],
            'Mandatory Fun'                                            =>
                [
                    'handy.html'                  => 'Handy',
                    'lameclaimtofame.html'        => 'Lame Claim To Fame',
                    'foil.html'                   => 'Foil',
                    'sportssong.html'             => 'Sports Song',
                    'wordcrimes.html'             => 'Word Crimes',
                    'myowneyes.html'              => 'My Own Eyes',
                    'nowthatswhaticallpolka.html' => 'Now That\'s What I Call Polka!',
                    'missionstatement.html'       => 'Mission Statement',
                    'inactive.html'               => 'Inactive',
                    'firstworldproblems.html'     => 'First World Problems',
                    'tacky.html'                  => 'Tacky',
                    'jacksonparkexpress.html'     => 'Jackson Park Express',
                ],
            'other songs:'                                             =>
                [
                    'amatterofcrust.html'                                        => 'A Matter Of Crust',
                    'avocado.html'                                               => 'Avocado',
                    'babylikesburping.html'                                      => 'Baby Likes Burping',
                    'badhombresnastywomen.html'                                  => 'Bad Hombres, Nasty Women',
                    'beatonthebrat.html'                                         => 'Beat On The Brat',
                    'belvederecruising.html'                                     => 'Belvedere Cruising',
                    'borntobemild.html'                                          => 'Born To Be Mild',
                    'burgerking.html'                                            => 'Burger King',
                    'captainunderpantsthemesong.html'                            => 'Captain Underpants Theme Song',
                    'cheeriosapplejackscheerios.html'                            => 'Cheerios, Apple Jacks, Cheerios',
                    'chickenpotpie.html'                                         => 'Chicken Pot Pie',
                    'christmasmemoriesofweirdalyankovic.html'                    => 'Christmas Memories Of "Weird Al" Yankovic',
                    'cramptoncomesalive.html'                                    => 'Crampton Comes Alive',
                    'deadcarbatteryblues.html'                                   => 'Dead Car Battery Blues',
                    'drdementoradiopromo.html'                                   => 'Dr. Demento Radio Promo',
                    'drdementos15thanniversaryspecial.html'                      => 'Dr. Demento\'s 15th Anniversary Special',
                    'fastfood.html'                                              => 'Fast Food',
                    'fatter.html'                                                => 'Fatter',
                    'flatbushavenue.html'                                        => 'Flatbush Avenue',
                    'foodmedleyasperformedin198501.html'                         => 'food medley as performed in 1985 - 01',
                    'foodmedleyasperformedin198502.html'                         => 'food medley as performed in 1985 - 02',
                    'foodmedleyasperformedin198503.html'                         => 'food medley as performed in 1985 - 03',
                    'foodmedleyasperformedin198504.html'                         => 'food medley as performed in 1985 - 04',
                    'foodmedleyasperformedin198505.html'                         => 'food medley as performed in 1985 - 05',
                    'foodmedleyasperformedin198506.html'                         => 'food medley as performed in 1985 - 06',
                    'foodmedleyasperformedin198507.html'                         => 'food medley as performed in 1985 - 07',
                    'foodmedleyasperformedin198508.html'                         => 'food medley as performed in 1985 - 08',
                    'foodmedleyasperformedin198509.html'                         => 'food medley as performed in 1985 - 09',
                    'foodmedleyasperformedin198510.html'                         => 'food medley as performed in 1985 - 10',
                    'foodmedleyasperformedin198511.html'                         => 'food medley as performed in 1985 - 11',
                    'foodmedleyasperformedin198512.html'                         => 'food medley as performed in 1985 - 12',
                    'foodmedleyasperformedin198513.html'                         => 'food medley as performed in 1985 - 13',
                    'freedelivery.html'                                          => 'Free Delivery',
                    'geeimanerd.html'                                            => 'Gee, I\'m A Nerd',
                    'gravyonyou.html'                                            => 'Gravy On You',
                    'greeneggsandham.html'                                       => 'Green Eggs And Ham',
                    'headlinenews.html'                                          => 'Headline News',
                    'heyheywerethemonks.html'                                    => 'Hey, Hey, We\'re The Monks',
                    'hitmewitharock.html'                                        => 'Hit Me With A Rock',
                    'holidaygreetings1987.html'                                  => 'Holiday Greetings 1987',
                    'holidaygreetings1988.html'                                  => 'Holiday Greetings 1988',
                    'homerandmarge.html'                                         => 'Homer And Marge',
                    'https://www.azlyrics.com/lyrics/katewinslet/ineedanap.html' => 'I Need A Nap',
                    'illrepairforyouathemeforhomeimprovement.html'               => 'I\'ll Repair For You ( A Theme For Home Improvement )',
                    'ificouldmakelovetoabottle.html'                             => 'If I Could Make Love To A Bottle',
                    'itsmyworldandwerealllivinginit.html'                        => 'It\'s My World (And We\'re All Living In It)',
                    'itsstillbillyjoeltome.html'                                 => 'It\'s Still Billy Joel To Me',
                    'kidstar1250radiopromotion.html'                             => 'Kidstar 1250 Radio Promotion',
                    'laundryday.html'                                            => 'Laundry Day',
                    'leisuresuitserenade.html'                                   => 'Leisure Suit Serenade',
                    'letthepunfitthecrime.html'                                  => 'Let The Pun Fit The Crime',
                    'lousyhaircut.html'                                          => 'Lousy Haircut',
                    'matterofcrust.html'                                         => 'Matter Of Crust',
                    'nevermetapersonaswonderfulasme.html'                        => 'Never Met A Person As Wonderful As Me',
                    'nobodyherebutusfrogs.html'                                  => 'Nobody Here But Us Frogs',
                    'nowyouknow.html'                                            => 'Now You Know',
                    'pacman.html'                                                => 'Pacman',
                    'polkapatterns.html'                                         => 'Polka Patterns',
                    'polkamania.html'                                            => 'Polkamania!',
                    'polkamon.html'                                              => 'Polkamon',
                    'realradio1041.html'                                         => 'RealRadio 104.1',
                    'scarifbeachparty.html'                                      => 'Scarif Beach Party',
                    'schoolcafeteriaversion1.html'                               => 'School Cafeteria - Version 1',
                    'schoolcafeteriaversion2.html'                               => 'School Cafeteria - Version 2',
                    'sirisaacnewtonvsbillnye.html'                               => 'Sir Isaac Newton Vs. Bill Nye',
                    'snackallnight.html'                                         => 'Snack All Night',
                    'sometimesyoufeellikeanut.html'                              => 'Sometimes You Feel Like A Nut',
                    'spyhard.html'                                               => 'Spy Hard',
                    'superduperpartypony.html'                                   => 'Super Duper Party Pony',
                    'takemedown.html'                                            => 'Take Me Down',
                    'takemetotheliver.html'                                      => 'Take Me To The Liver',
                    'taketheloutofliver.html'                                    => 'Take The "L" Out Of Liver',
                    'theballadofkentmarlow.html'                                 => 'The Ballad Of Kent Marlow',
                    'thebrainsong.html'                                          => 'The Brain Song',
                    'thehamiltonpolka.html'                                      => 'The Hamilton Polka',
                    'thenightsantawentcrazyextragory.html'                       => 'The Night Santa Went Crazy (Extra Gory)',
                    'thenorthkoreapolkapleasedontnukeus.html'                    => 'The North Korea Polka (Please Don\'t Nuke Us)',
                    'wegotthebeef.html'                                          => 'We Got The Beef',
                    'wonteatprunesagain.html'                                    => 'Won\'t Eat Prunes Again',
                    'yodachant.html'                                             => 'Yoda Chant',
                    'youdonttakeyourshowers.html'                                => 'You Don\'t Take Your Showers',
                    'yourepitiful.html'                                          => 'You\'re Pitiful',
                ],
        ]);
    }

    private function extractLyrics($html): string
    {
        $partialHtml = explode('<!-- content -->', $html);

        $domIwant = $partialHtml[1] ?? '';

        if (blank($domIwant)) {
            return '';
        }

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($domIwant);
        libxml_clear_errors();

        foreach ($dom->getElementsByTagName('div') as $div) {
            if (!$div->hasAttribute('class')) {
                return $div->textContent;
            }

            if (str_contains($div->textContent, 'Usage of azlyrics')) {
                return $div->textContent;
            }
        }

        return '';
    }
}
