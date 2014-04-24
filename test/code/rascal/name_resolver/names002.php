<?php

namespace Animal {
    class Ape {}
    class Bear {}
}

namespace Animal\Bear {
    class Panda {}
}

namespace Animal\Bear\Panda {
    class GiantPanda {}
}

namespace Car {
    class Panda {}
}

namespace Random {
    use Animal\Bear;
    use Car\Panda;
    $bear = new Bear;
    $pandaBear = new Bear\Panda;
    $giantPandaBear = new Bear\Panda\GiantPanda;
    $giantPandaBear = new \Animal\Bear\Panda\GiantPanda;
    $pandaCar = new Panda;
    $pandaCar = new \Car\Panda;
}
