<?php
namespace SuperSmashLore\SuperSmashLore\Test;

//use Ramsey\Uuid\Uuid;
use SuperSmashLore\SuperSmashLore\{Game, Character};

//grab the class under scrutiny
require_once(dirname(__DIR__) . "/autoloader.php");
//grab the uuid generator
require_once (dirname(__DIR__, 2) . "/lib/uuid.php");
/**
 * Full PHPUnit test of Game class. It is complete because all mySQL/PDO enabled methods are tested for both invalid and valid inputs
 *
 * @see Game
 * @author Josh Mashke <jmashke@cnm.edu>
 **/
class GameTest extends SuperSmashLoreTest {
	/**
	 * game Id
	 * @var Uuid game Id
	 */
	//protected $game;
	/**
	 * game that a character comes from; This is for foreign kew relations
	 **/
	protected $character;
	 /**
	  * valid picture url
	  * @var string $VALID_PICTURE_URL
	  **/
	protected $VALID_PICTURE_URL = "https://www.google.com";

	/**
	 *valid game system
	 * @var string $VALID_GAME_SYSTEM
	 **/
	protected $VALID_GAME_SYSTEM = "Nintendo Switch";

	/**
	 * valid game url
	 * @var string $VALID_GAME_URL
	 **/
	protected $VALID_GAME_URL = "https://www.wergsdv.com";

	/**
	 * create dependent objects before running each test
	 **/
	public final function setUp() : void {
		// create and insert character to own the test game
		$characterId = generateUuidV4();
		$this->character = new Character( $characterId,"The King of Thieves", "https://characterMusic.com", "britneyBitch",
			"https://www.google.com", "whatever", "right now", "Gerudo Theme", "iurgieuni");
		$this->character->insert($this->getPDO());
	}

	/**
	 *test inserting a valid game and verifying that the actual mySQL data matches
	 **/
	public function testInsertValidGame() : void {

		//create a new Game and insert into mySQL
		$gameId = generateUuidV4();
		$game = new Game($gameId, $this->character->getCharacterId(),  $this->VALID_PICTURE_URL, $this->VALID_GAME_SYSTEM, $this->VALID_GAME_URL);
		$game->insert($this->getPDO());

		//grb the data from mySQL nd enforce the fields match our expectations
		$pdoGame = Game::getGameByGameId($this->getPDO(), $game->getGameId());
		$this->assertEquals($pdoGame->getGameId()->toString(), $gameId->toString());
		$this->assertEquals($pdoGame->getGameCharacterId(), $game->getGameCharacterId()->toString());
		$this->assertEquals($pdoGame->getGamePicture(), $this->VALID_PICTURE_URL);
		$this->assertEquals($pdoGame->getGameSystem(), $this->VALID_GAME_SYSTEM);
		$this->assertEquals($pdoGame->getGameUrl(), $this->VALID_GAME_URL);


	}

	/**
	 * test creating game and then deleting it
	 **/
	public function testDeleteValidGame() : void {
		//count the number of rows and save it for later
		$numRows = $this->getConnection()->getRowCount("game");

		//create a new game and insert it into mySQL
		$gameId = generateUuidV4();
		$game = new Game($gameId, $this->character->getCharacterId(), $this->VALID_PICTURE_URL, $this->VALID_GAME_SYSTEM,  $this->VALID_GAME_URL);
		$game->insert($this->getPDO());

		//delete the game from mySQL
		$this->assertEquals($numRows + 1, $this->getConnection()->getRowCount("game"));
		$game->delete($this->getPDO());

		//grab data from mySQL and enforce the game does not exist
		$pdoGame = Game::getGameByGameId($this->getPDO(), $game->getGameId());
		$this->assertNull($pdoGame);
		$this->assertEquals($numRows, $this->getConnection()->getRowCount("game"));
	}

	/**
	 * test inserting a game and grabbing it from mySQL
	 **/
	public function testGetValidGameByGameCharacterId () : void {
		// count the number of rows
		$numRows = $this->getConnection()->getRowCount("game");

		//create a new game and insert into mySQL
		$gameId = generateUuidV4();
		$game = new Game($gameId, $this->character->getCharacterId(), $this->VALID_PICTURE_URL, $this->VALID_GAME_SYSTEM, $this->VALID_GAME_URL );
		$game->insert($this->getPDO());

		//grab the data from mySQL and enforce the fields match expectations
		$results = Game::getGameByGameCharacterId($this->getPDO(), $game->getGameCharacterId());
		$this->assertEquals($numRows + 1, $this->getConnection()->getRowCount("game"));
		$this->assertCount(1, $results);
		$this->assertContainsOnlyInstancesOf("SuperSmashLore\\SuperSmashLore\\Game", $results);

		//grab the result from array and validate it
		$pdoGame = $results[0];

		$this->assertEquals($pdoGame->getGameId(), $gameId);
		$this->assertEquals($pdoGame->getGameCharacterId(), $this->character->getCharacterId());
	}

	/**
	 * test grabbing all games
	 **/
	public function testGetAllValidGames() : void {
		//count number of rows and ave it for later
		$numRows = $this->getConnection()->getRowCount("game");

		//create new game and insert into mySQL
		$gameId = generateUuidV4();
		$game = new Game($gameId, $this->character->getCharacterId(), $this->VALID_PICTURE_URL, $this->VALID_GAME_SYSTEM, $this->VALID_GAME_URL);
		$game->insert($this->getPDO());

		//grab the data from mySQL adn enforce the fields match expectations
		$results = Game::getAllGames($this->getPDO());
		$this->assertEquals($numRows + 1, $this->getConnection()->getRowCount("game"));
		$this->assertCount(1, $results);
		$this->assertContainsOnlyInstancesOf("SuperSmashLore\\SuperSmashLore\\Game", $results);

		//grab the result from array and validate it
		$pdoGame = $results[0];

		//grab results from array and validate it
		$this->assertEquals($pdoGame->getGameId(), $gameId);
		$this->assertEquals($pdoGame->getGameCharacterId(), $this->character->getCharacterId());
	}

}