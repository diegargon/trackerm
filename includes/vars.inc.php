<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
/*
 * 400: 7
 * 365: 7
 * 250: 12
 * 200: 31
 * 100: 198
 * 50: 1334
 * 15: 41000
 */
$galaxy_tpl = [
    'galaxy_min_spacer' => 60 * 24 * 15,
    'galaxy_maxsize_from_sun' => 60 * 24 * 7,
    'galaxy_min_planets' => 6,
    'galaxy_max_planets' => 12,
    'planet_min_spacer' => 60 * 24 * 2,
        /* 'planet_max_spacer' => 60 * 24 * 4, */
];

$planet_table = [
    1 => 1000000,
    2 => 2000000,
    3 => 3000000,
    4 => 4000000,
    5 => 5000000,
    6 => 6000000,
    7 => 7000000,
    8 => 8000000,
];

$character_names = [
    'Oliver', 'Jack', 'Harry', 'Jacob', 'Charlie', 'Thomas', 'George',
    'Oscar', 'James', 'Willian', 'Jake', 'Kyle', 'Joe', 'Damian', 'Noah',
    'Liam', 'Willian', 'Ethan', 'Michael', 'Alexander', 'John', 'Daniel',
    'Richard', 'David', 'Joseph', 'Margaret', 'Samantha', 'Elizabeth',
    'Megan', 'Victoria', 'Michelle', 'Tracy', 'Madison', 'Charlotte',
    'Mary', 'Linda', 'Susan', 'Sarah', 'Amelia', 'Olivia', 'Isla',
    'Emily', 'Poppy', 'Ava', 'Isabella', 'Jessica', 'Lily', 'Sophie',
    'Diego', 'Santiago', 'Felix', 'Ivan',
];

$character_surnames = [
    'Smith', 'Jones', 'Willians', 'Brown', 'Taylor', 'Davies', 'Wilson', 'Roberts',
    'Li', 'Murphy', 'Walsh', 'Ryan', 'Connor', 'Miller', 'Garcia', 'Rodriguez',
    'Lam', 'Lee', 'White', 'Anderson', 'Wang', 'Singh', 'Gonzalez', 'Varela', 'Castro',
];


$perks = [
    1 => 'L_PILOT', //Piloto naves necesario para pilotar ¿mejora? probabilidad de daño motor, esquivar tiros
    //Necesario en todas las naves
    2 => 'L_PILOT_COMBAT', //Piloto nave y artillero
    //Necesario en naves de combate personales
    3 => 'L_GUNNER', //Punteria
    4 => 'L_MECHANIC', //Mecanico matenimiento de motores
    5 => 'L_EXPLORER', //Explorador mejora radar? para ello el radar no puede tener 100% efectividad ¿distancia?
    // lo ideal que la efectividad del radar dependiera de la distancia
    6 => 'L_SCIENTIFIC', //Mejora Investigacion
    7 => 'L_ENGINIEER', //Extructuras/Shipyard
];


/*
 * Research
 *


  /* Ships
 *
 * Puede utilizar los perforadores
 * Veloidad Maxima 1tick 2ticks 3ticks
 * Aceleracion: depende de la masa
 *      1m se pone a max en 1tick
 *      2m se pone a max en 2
 * Cargo: opcional : carga/gente
 */


$ship_parts['bridge'][1] = [
    'name' => 'Bridge P1',
    'desc' => 'desc',
    'cap' => 1,
    'mass' => 0.2,
];
$ship_parts['bridge'][2] = [
    'name' => 'Bridge P2',
    'desc' => 'desc',
    'cap' => 2,
    'mass' => 1,
];
$ship_parts['bridge'][3] = [
    'name' => 'Bridge P3',
    'desc' => 'desc',
    'cap' => 4,
    'mass' => 2,
];
$ship_parts['bridge'][4] = [
    'name' => 'Bridge P4',
    'desc' => 'desc',
    'cap' => 8,
    'mass' => 4,
];

$ship_parts['crew'][1] = [
    'name' => 'Crew P100',
    'desc' => 'desc',
    'cap' => 100,
    'mass' => 5,
];
$ship_parts['crew'][2] = [
    'name' => 'Crew P200',
    'desc' => 'desc',
    'cap' => 200,
    'mass' => 8,
];

$ship_parts['cargo'][1] = [
    'name' => 'Cargo C1000',
    'desc' => 'desc',
    'cap' => 1000,
    'mass' => 1,
];
$ship_parts['cargo'][2] = [
    'name' => 'Cargo C2000',
    'desc' => 'desc',
    'cap' => 2000,
    'mass' => 2,
];

$ship_parts['ships_cargo'][1] = [
    'name' => 'Ships cargo SC100',
    'desc' => 'desc',
    'cap' => 100,
    'mass' => 10,
];
$ship_parts['ships_cargo'][2] = [
    'name' => 'Cargo SC200',
    'desc' => 'desc',
    'cap' => 200,
    'mass' => 20,
];

$ship_parts['missile_storage'][1] = [
    'name' => 'Missile Storage MS2',
    'desc' => 'desc',
    'cap' => 2,
    'mass' => 1,
];
$ship_parts['missile_storage'][2] = [
    'name' => 'Missile Storage MS4',
    'desc' => 'desc',
    'cap' => 4,
    'mass' => 2,
];

$ship_parts['front_guns'][1] = [
    'name' => 'Front Gunner FG-1',
    'desc' => 'desc',
    'cadence' => 4,
    'cap' => 1,
    'mass' => 0.5,
];
$ship_parts['front_guns'][2] = [
    'name' => 'Front Gunner FG-2',
    'desc' => 'desc',
    'cadence' => 8,
    'cap' => 2,
    'mass' => 1,
];

$ship_parts['turrets'][1] = [
    'name' => 'Turret T-1',
    'desc' => 'desc',
    'cadence' => 8,
    'cap' => 1,
    'mass' => 1,
];

$ship_parts['turrets'][2] = [
    'name' => 'Turret T-2',
    'desc' => 'desc',
    'cadence' => 16,
    'cap' => 2,
    'mass' => 2,
];

/* Power puede ser 1power para mover 1masa */
$ship_parts['propeller'][1] = [
    'name' => 'Propeller P-10',
    'desc' => 'desc',
    'power' => 10,
    'energy' => 100,
    'mass' => 1,
];
$ship_parts['propeller'][2] = [
    'name' => 'Propeller P-20',
    'desc' => 'desc',
    'power' => 20,
    'energy' => 200,
    'mass' => 2,
];

$ship_parts['accumulator'][1] = [
    'name' => 'Accumulator A2000',
    'desc' => 'desc',
    'cap' => 2000,
    'mass' => 2,
];

$ship_parts['accumulator'][2] = [
    'name' => 'Accumulator A4000',
    'desc' => 'desc',
    'cap' => 4000,
    'mass' => 4,
];

$ship_parts['generator'][1] = [
    'name' => 'Generator FG1',
    'desc' => 'desc',
    'power_rad' => 0, //1*radiation per tick
    'power_fuel' => 10, // per_tick
    'fuel_intake' => 1,
    'mass' => 1,
];

$ship_parts['generator'][2] = [
    'name' => 'Generator FG2',
    'desc' => 'desc',
    'power_rad' => 0, //1*radiation per tick
    'power_fuel' => 20, // per_tick
    'fuel_intake' => 1,
    'mass' => 2,
];

$ship_parts['generator'][3] = [
    'name' => 'Generator SG1',
    'desc' => 'desc',
    'power_rad' => 10, //1*radiation per tick
    'power_fuel' => 0, // per_tick
    'fuel_intake' => 0,
    'mass' => 0.5,
];

$ship_parts['generator'][4] = [
    'name' => 'Generator SG2',
    'desc' => 'desc',
    'power_rad' => 20, //1*radiation per tick
    'power_fuel' => 0, // per_tick
    'fuel_intake' => 0,
    'mass' => 1,
];

$ship_parts['generator'][5] = [
    'name' => 'Generator Dual FSG-1',
    'desc' => 'desc',
    'power_rad' => 10, //1*radiation per tick
    'power_fuel' => 10, // per_tick
    'fuel_intake' => 1,
    'mass' => 3,
];

$ship_parts['shields'][1] = [
    'name' => 'Shields S1',
    'desc' => 'desc',
    'energy' => 20, //per tick 100% cover shields per missile inpact   /2 other impact
    'mass' => 0.5,
];

$ship_parts['radar'][1] = [
    'name' => 'Radar D5',
    'desc' => 'desc',
    'cap' => 5,
    'mass' => 0.5,
];

//MATS
$cargo_types[1] = 'L_TITANIUM';
$cargo_types[2] = 'L_LITHIUM';
$cargo_types[3] = 'L_ARMATITA';
