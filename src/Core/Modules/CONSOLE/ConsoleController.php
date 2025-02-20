<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\CONSOLE;

use Nadybot\Core\{
	BotRunner,
	CommandManager,
	EventManager,
	LoggerWrapper,
	Nadybot,
	SettingManager,
	SocketManager,
	SocketNotifier,
	Timer,
};

/**
 * @Instance
 */
class ConsoleController {
	/**
	 * Name of the module.
	 * Set automatically by module loader.
	 */
	public string $moduleName;

	/** @Inject */
	public SocketManager $socketManager;

	/** @Inject */
	public EventManager $eventManager;

	/** @Inject */
	public CommandManager $commandManager;

	/** @Inject */
	public SettingManager $settingManager;

	/** @Inject */
	public Nadybot $chatBot;

	/** @Inject */
	public Timer $timer;

	/** @Logger */
	public LoggerWrapper $logger;

	public SocketNotifier $notifier;

	public $socket;

	public bool $useReadline = false;

	public function getCacheFile(): string {
		if (isset($_SERVER["XDG_CACHE_HOME"])) {
			return explode(":", $_SERVER["XDG_CACHE_HOME"])[0] . "/Nadybot/readline.history";
		}
		if (isset($_SERVER["HOME"])) {
			return $_SERVER["HOME"] . "/.cache/Nadybot/readline.history";
		}
		return sys_get_temp_dir() . "/Nadybot/readline.history";
	}

	public function loadHistory(): bool {
		$file = $this->getCacheFile();
		if (!@file_exists($file)) {
			return false;
		}
		return readline_read_history($file);
	}

	public function saveHistory(): bool {
		$file = $this->getCacheFile();
		if (!@file_exists($file)) {
			if (!@mkdir(dirname($file), 0700, true)) {
				return false;
			}
		}
		return readline_write_history($file);
	}

	/**
	 * @Event("connect")
	 * @Description("Initializes the console")
	 * @DefaultStatus("1")
	 *
	 * This is an Event("connect") instead of Setup since you cannot use the console
	 * before the bot is fully ready anyway
	 */
	public function setupConsole(): void {
		if (!$this->chatBot->vars["enable_console_client"]) {
			return;
		}
		if (BotRunner::isWindows()) {
			$this->logger->log('WARN', 'Console not available on Windows');
			return;
		}
		$this->useReadline = function_exists('readline_callback_handler_install');
		if (!$this->useReadline) {
			$this->logger->log('WARN', 'readline not supported on this platform, using basic console');
			$callback = [$this, "processStdin"];
		} else {
			$callback = function(): void {
				readline_callback_read_char();
			};
		}
		$this->loadHistory();
		$this->socket = STDIN;
		$this->notifier = new SocketNotifier(
			$this->socket,
			SocketNotifier::ACTIVITY_READ,
			$callback,
		);
		$this->timer->callLater(1, function(): void {
			$this->logger->log('INFO', "StdIn console activated, accepting commands");
			$this->socketManager->addSocketNotifier($this->notifier);
			if ($this->useReadline) {
				readline_callback_handler_install('> ', [$this, 'processLine']);
			} else {
				echo("> ");
			}
		});
	}

	/**
	 * Handle data arriving on stdin
	 */
	public function processStdin(): void {
		if (feof($this->socket)) {
			echo("EOF received, closing console.\n");
			@fclose($this->socket);
			$this->socketManager->removeSocketNotifier($this->notifier);
			return;
		}
		$line = fgets($this->socket);
		if ($line !== false) {
			$this->processLine(trim($line));
			echo("> ");
		}
	}

	public function processLine(?string $line): void {
		if ($line === null || trim($line) === '') {
			return;
		}
		if (substr($line, 0, 1) === $this->settingManager->getString('symbol')) {
			$line = substr($line, 1);
			if (trim($line) === '') {
				return;
			}
		}
		if ($this->useReadline) {
			readline_add_history($line);
			$this->saveHistory();
		}
		$handler = new ConsoleCommandReply($this->chatBot);
		$this->commandManager->process("msg", $line, $this->chatBot->vars["SuperAdmin"], $handler);
	}
}
