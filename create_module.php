<?php

if (isset($argv[1])) {
  $moduleName = $argv[1];
} else {
  echo 'Introduce el nombre del nuevo módulo: ';
  $moduleName = trim(fgets(STDIN));
}

if (empty($moduleName)) {
  echo "El nombre del módulo no puede estar vacío.\n";
  exit(1);
}

$moduleName = ucfirst(trim($moduleName, '"\''));

$pluralMap = [
  'y' => 'ies',
  's' => 'ses',
  'z' => 'ces',
];
$lastChar = substr($moduleName, -1);
$pluralSuffix = $pluralMap[$lastChar] ?? 's';

$basePath = __DIR__ . '/app/Modules/' . $moduleName;

if (file_exists($basePath)) {
  echo "El módulo {$moduleName} ya existe.\n";
  exit(1);
}

echo "Creando el módulo {$moduleName}...\n";

$directories = [
  'Controllers',
  'Migrations',
  'Models',
  'Providers',
  'Repositories',
  'Requests',
  'UseCases',
  'msg',
  'Traits',
];

foreach ($directories as $dir) {
  $path = $basePath . '/' . $dir;
  if (!mkdir($path, 0755, true)) {
    echo "Error al crear el directorio: {$path}\n";
    exit(1);
  }
}

$lcModule = lcfirst($moduleName);
$lcPlural = lcfirst($moduleName) . $pluralSuffix;
$migrationTimestamp = date('Y_m_d_His');

// routes.php
$routesContent = <<<EOD
<?php

use App\\Modules\\{$moduleName}\\Controllers\\{$moduleName}Controller;
use Illuminate\\Support\\Facades\\Route;

Route::get('/', [{$moduleName}Controller::class, 'index'])->middleware('jwt.cookie');
Route::post('/', [{$moduleName}Controller::class, 'create'])->middleware('jwt.cookie');
Route::get('/{uid}', [{$moduleName}Controller::class, 'show'])->middleware('jwt.cookie');
Route::put('/{uid}', [{$moduleName}Controller::class, 'update'])->middleware('jwt.cookie');
Route::delete('/{uid}', [{$moduleName}Controller::class, 'destroy'])->middleware('jwt.cookie');

EOD;
file_put_contents($basePath . '/routes.php', $routesContent);

// msg/msg.php
$msgContent = <<<EOD
<?php

return [
  '{$lcModule}' => [
    'created' => '{$moduleName} creado con éxito',
    'retrieved' => '{$moduleName} recuperado con éxito',
    'updated' => '{$moduleName} actualizado con éxito',
    'deleted' => '{$moduleName} eliminado con éxito',
    'not_found' => '{$moduleName} no encontrado',
    'creation_error' => 'Error al crear {$moduleName}',
    'retrieval_error' => 'Error al recuperar {$moduleName}',
    'update_error' => 'Error al actualizar {$moduleName}',
    'deletion_error' => 'Error al eliminar {$moduleName}',
  ],
  'validation' => [
    'create' => [],
    'update' => [],
  ],
];
EOD;
file_put_contents($basePath . '/msg/msg.php', $msgContent);

// msg/useMsg.php
$useMsgContent = <<<EOD
<?php

namespace App\\Modules\\{$moduleName}\\msg;

class useMsg
{
  protected static array \$cache = [];

  public static function get(string \$key, array \$replace = []): string
  {
    \$locale = 'es';

    if (!isset(self::\$cache[\$locale])) {
      \$file = __DIR__ . '/msg.php';

      if (file_exists(\$file)) {
        self::\$cache[\$locale] = require \$file;
      } else {
        self::\$cache[\$locale] = [];
      }
    }

    \$parts = explode('.', \$key);
    \$data = self::\$cache[\$locale];

    foreach (\$parts as \$part) {
      if (!is_array(\$data) || !array_key_exists(\$part, \$data)) {
        return \$key;
      }
      \$data = \$data[\$part];
    }

    \$message = is_string(\$data) ? \$data : \$key;

    foreach (\$replace as \$placeholder => \$value) {
      \$message = str_replace(':' . \$placeholder, \$value, \$message);
    }

    return \$message;
  }
}
EOD;
file_put_contents($basePath . '/msg/useMsg.php', $useMsgContent);

// Model
$modelContent = <<<EOD
<?php

namespace App\\Modules\\{$moduleName}\\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class {$moduleName} extends Model
{
  use HasFactory;

  protected \$table = '{$lcPlural}';

  protected \$primaryKey = 'uid';

  public \$incrementing = false;

  protected \$keyType = 'string';

  protected \$fillable = ['uid', 'name'];

  protected \$visible = ['uid', 'name'];
}
EOD;
file_put_contents($basePath . "/Models/{$moduleName}.php", $modelContent);

// Migration
$migrationContent = <<<EOD
<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('{$lcPlural}', function (Blueprint \$table) {
      \$table->uuid('uid')->primary();
      \$table->string('name');
      \$table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('{$lcPlural}');
  }
};
EOD;
file_put_contents($basePath . "/Migrations/{$migrationTimestamp}_create_{$lcPlural}_table.php", $migrationContent);

// Controller
$controllerContent = <<<EOD
<?php

namespace App\\Modules\\{$moduleName}\\Controllers;

use App\\Http\\Controllers\\Controller;
use App\\Modules\\{$moduleName}\\UseCases\\FindAll{$moduleName};
use App\\Modules\\{$moduleName}\\UseCases\\FindByUid{$moduleName};
use App\\Modules\\{$moduleName}\\UseCases\\Create{$moduleName};
use App\\Modules\\{$moduleName}\\UseCases\\Update{$moduleName};
use App\\Modules\\{$moduleName}\\UseCases\\Delete{$moduleName};
use App\\Modules\\{$moduleName}\\Requests\\Create{$moduleName}Request;
use App\\Modules\\{$moduleName}\\Requests\\Update{$moduleName}Request;

class {$moduleName}Controller extends Controller
{
  public function __construct(
    private FindAll{$moduleName} \$findAllUseCase,
    private FindByUid{$moduleName} \$findByUidUseCase,
    private Create{$moduleName} \$createUseCase,
    private Update{$moduleName} \$updateUseCase,
    private Delete{$moduleName} \$deleteUseCase,
  ) {}

  public function index()
  {
    \$result = \$this->findAllUseCase->execute();
    return response()->json(\$result);
  }

  public function show(string \$uid)
  {
    \$result = \$this->findByUidUseCase->execute(\$uid);

    if (!\$result['success']) {
      return response()->json(\$result, \$result['statusCode']);
    }

    return response()->json(\$result);
  }

  public function create(Create{$moduleName}Request \$request)
  {
    \$result = \$this->createUseCase->execute(\$request->validated());

    if (!\$result['success']) {
      return response()->json(\$result, \$result['statusCode']);
    }

    return response()->json(\$result, 201);
  }

  public function update(Update{$moduleName}Request \$request, string \$uid)
  {
    \$result = \$this->updateUseCase->execute(\$uid, \$request->validated());

    if (!\$result['success']) {
      return response()->json(\$result, \$result['statusCode']);
    }

    return response()->json(\$result);
  }

  public function destroy(string \$uid)
  {
    \$result = \$this->deleteUseCase->execute(\$uid);

    if (!\$result['success']) {
      return response()->json(\$result, \$result['statusCode']);
    }

    return response()->json(\$result);
  }
}
EOD;
file_put_contents(
  $basePath . "/Controllers/{$moduleName}Controller.php",
  $controllerContent,
);

// Service Provider
$providerContent = <<<EOD
<?php

namespace App\\Modules\\{$moduleName}\\Providers;

use Illuminate\\Support\\ServiceProvider;
use App\\Modules\\{$moduleName}\\Repositories\\{$moduleName}RepositoryInterface;
use App\\Modules\\{$moduleName}\\Repositories\\{$moduleName}Repository;
use App\\Modules\\{$moduleName}\\Repositories\\{$moduleName}RepositoryCacheDecorator;
use Illuminate\\Contracts\\Cache\\Repository as CacheRepository;

class {$moduleName}ServiceProvider extends ServiceProvider
{
  protected \$namespace = 'App\\\\Modules\\\\{$moduleName}\\\\Controllers';

  public function register()
  {
    \$this->app->bind({$moduleName}RepositoryInterface::class, function (\$app) {
      \$repo = \$app->make({$moduleName}Repository::class);
      \$cache = \$app->make(CacheRepository::class);
      \$ttl = (int) config('cache.{$lcModule}_ttl', 300);

      return new {$moduleName}RepositoryCacheDecorator(\$repo, \$cache, \$ttl);
    });
  }

  public function boot()
  {
    \$this->loadMigrationsFrom(__DIR__ . '/../Migrations');
  }
}
EOD;
file_put_contents(
  $basePath . "/Providers/{$moduleName}ServiceProvider.php",
  $providerContent,
);

// Repository Interface
$repoInterfaceContent = <<<EOD
<?php

namespace App\\Modules\\{$moduleName}\\Repositories;

use App\\Modules\\{$moduleName}\\Models\\{$moduleName};
use Illuminate\\Contracts\\Pagination\\LengthAwarePaginator;

interface {$moduleName}RepositoryInterface
{
  public function create(array \$data): {$moduleName};

  public function findByUid(string \$uid): ?{$moduleName};

  public function update({$moduleName} \${$lcModule}, array \$data): {$moduleName};

  public function delete({$moduleName} \${$lcModule}): bool;

  public function paginate(int \$perPage = 20): LengthAwarePaginator;
}
EOD;
file_put_contents(
  $basePath . "/Repositories/{$moduleName}RepositoryInterface.php",
  $repoInterfaceContent,
);

// Repository
$repoContent = <<<EOD
<?php

namespace App\\Modules\\{$moduleName}\\Repositories;

use App\\Modules\\{$moduleName}\\Models\\{$moduleName};
use Illuminate\\Contracts\\Pagination\\LengthAwarePaginator;
use Illuminate\\Support\\Str;

class {$moduleName}Repository implements {$moduleName}RepositoryInterface
{
  public function create(array \$data): {$moduleName}
  {
    if (!isset(\$data['uid'])) {
      \$data['uid'] = (string) Str::uuid();
    }

    return {$moduleName}::create(\$data);
  }

  public function findByUid(string \$uid): ?{$moduleName}
  {
    return {$moduleName}::find(\$uid);
  }

  public function update({$moduleName} \${$lcModule}, array \$data): {$moduleName}
  {
    \${$lcModule}->fill(\$data);
    \${$lcModule}->save();

    return \${$lcModule};
  }

  public function delete({$moduleName} \${$lcModule}): bool
  {
    return (bool) \${$lcModule}->delete();
  }

  public function paginate(int \$perPage = 20): LengthAwarePaginator
  {
    return {$moduleName}::paginate(\$perPage);
  }
}
EOD;
file_put_contents(
  $basePath . "/Repositories/{$moduleName}Repository.php",
  $repoContent,
);

// Repository Cache Decorator
$cacheDecoratorContent = <<<EOD
<?php

namespace App\\Modules\\{$moduleName}\\Repositories;

use App\\Modules\\{$moduleName}\\Models\\{$moduleName};
use Illuminate\\Contracts\\Cache\\Repository as CacheRepository;
use Illuminate\\Contracts\\Pagination\\LengthAwarePaginator;

class {$moduleName}RepositoryCacheDecorator implements {$moduleName}RepositoryInterface
{
  protected {$moduleName}RepositoryInterface \$repo;

  protected CacheRepository \$cache;

  protected int \$ttl;

  public function __construct(
    {$moduleName}RepositoryInterface \$repo,
    CacheRepository \$cache,
    int \$ttl = 300,
  ) {
    \$this->repo = \$repo;
    \$this->cache = \$cache;
    \$this->ttl = \$ttl;
  }

  public function create(array \$data): {$moduleName}
  {
    return \$this->repo->create(\$data);
  }

  public function findByUid(string \$uid): ?{$moduleName}
  {
    \$key = "{$lcModule}:uid:{\$uid}";

    return \$this->cache->remember(\$key, \$this->ttl, function () use (\$uid) {
      return \$this->repo->findByUid(\$uid);
    });
  }

  public function update({$moduleName} \${$lcModule}, array \$data): {$moduleName}
  {
    \$updated = \$this->repo->update(\${$lcModule}, \$data);
    \$this->flushEntityCaches(\$updated);

    return \$updated;
  }

  public function delete({$moduleName} \${$lcModule}): bool
  {
    \$result = \$this->repo->delete(\${$lcModule});

    if (\$result) {
      \$this->flushEntityCaches(\${$lcModule});
    }

    return \$result;
  }

  public function paginate(int \$perPage = 20): LengthAwarePaginator
  {
    \$page = request()->get('page', 1);
    \$key = "{$lcModule}:paginate:per:{\$perPage}:page:{\$page}";

    return \$this->cache->remember(\$key, \$this->ttl, function () use (\$perPage) {
      return \$this->repo->paginate(\$perPage);
    });
  }

  protected function flushEntityCaches({$moduleName} \${$lcModule}): void
  {
    try {
      \$this->cache->forget("{$lcModule}:uid:{\${$lcModule}->uid}");
    } catch (\\Throwable \$e) {
    }
  }
}
EOD;
file_put_contents(
  $basePath . "/Repositories/{$moduleName}RepositoryCacheDecorator.php",
  $cacheDecoratorContent,
);

// BaseRequest
$baseRequestContent = <<<EOD
<?php

namespace App\\Modules\\{$moduleName}\\Requests;

use App\\Modules\\{$moduleName}\\msg\\useMsg;
use Illuminate\\Foundation\\Http\\FormRequest;

abstract class BaseRequest extends FormRequest
{
  abstract protected function getValidationAction(): string;

  protected function prepareForValidation(): void
  {
    \$input = \$this->all();
    \$this->replace(\$this->sanitize(\$input));
  }

  protected function sanitize(array \$data): array
  {
    \$allowedFields = \$this->allowedFields();

    if (empty(\$allowedFields)) {
      return \$data;
    }

    return array_intersect_key(\$data, array_flip(\$allowedFields));
  }

  protected function allowedFields(): array
  {
    return [];
  }

  public function authorize(): bool
  {
    return true;
  }

  protected function failedValidation(\Illuminate\\Contracts\\Validation\\Validator \$validator)
  {
    \$errors = \$validator->errors()->toArray();
    \$formatted = [];

    foreach (\$errors as \$field => \$messages) {
      \$formatted[\$field] = ['message' => \$messages[0]];
    }

    throw new \Illuminate\\Validation\\ValidationException(
      \$validator,
      response()->json(\$formatted, 422),
    );
  }
}
EOD;
file_put_contents(
  $basePath . "/Requests/BaseRequest.php",
  $baseRequestContent,
);

// CreateRequest
$createRequestContent = <<<EOD
<?php

namespace App\\Modules\\{$moduleName}\\Requests;

class Create{$moduleName}Request extends BaseRequest
{
  protected function getValidationAction(): string
  {
    return 'create';
  }

  public function rules(): array
  {
    return [
      // 'name' => 'required|string|max:255',
    ];
  }

  public function messages(): array
  {
    return [];
  }
}
EOD;
file_put_contents(
  $basePath . "/Requests/Create{$moduleName}Request.php",
  $createRequestContent,
);

// UpdateRequest
$updateRequestContent = <<<EOD
<?php

namespace App\\Modules\\{$moduleName}\\Requests;

class Update{$moduleName}Request extends BaseRequest
{
  protected function getValidationAction(): string
  {
    return 'update';
  }

  public function rules(): array
  {
    return [
      // 'name' => 'sometimes|required|string|max:255',
    ];
  }

  public function messages(): array
  {
    return [];
  }
}
EOD;
file_put_contents(
  $basePath . "/Requests/Update{$moduleName}Request.php",
  $updateRequestContent,
);

// UseCase - Create
$createUseCaseContent = <<<EOD
<?php

namespace App\\Modules\\{$moduleName}\\UseCases;

use App\\Modules\\{$moduleName}\\Repositories\\{$moduleName}RepositoryInterface;
use App\\Modules\\{$moduleName}\\msg\\useMsg;

class Create{$moduleName}
{
  public function __construct(
    private {$moduleName}RepositoryInterface \$repository,
  ) {}

  public function execute(array \$data): array
  {
    try {
      \$entity = \$this->repository->create(\$data);

      return [
        'success' => true,
        'data' => \$entity,
        'message' => useMsg::get('{$lcModule}.created'),
      ];
    } catch (\\Exception \$e) {
      return [
        'success' => false,
        'statusCode' => 500,
        'message' => useMsg::get('{$lcModule}.creation_error'),
      ];
    }
  }
}
EOD;
file_put_contents(
  $basePath . "/UseCases/Create{$moduleName}.php",
  $createUseCaseContent,
);

// UseCase - FindAll
$findAllUseCaseContent = <<<EOD
<?php

namespace App\\Modules\\{$moduleName}\\UseCases;

use App\\Modules\\{$moduleName}\\Repositories\\{$moduleName}RepositoryInterface;
use App\\Modules\\{$moduleName}\\msg\\useMsg;

class FindAll{$moduleName}
{
  public function __construct(
    private {$moduleName}RepositoryInterface \$repository,
  ) {}

  public function execute(int \$perPage = 20): array
  {
    try {
      \$paginator = \$this->repository->paginate(\$perPage);

      return [
        'success' => true,
        'data' => [
          'rows' => \$paginator->items(),
          'count' => \$paginator->total(),
          'currentPage' => \$paginator->currentPage(),
          'nextPage' => \$paginator->hasMorePages() ? \$paginator->currentPage() + 1 : null,
          'previousPage' => \$paginator->currentPage() > 1 ? \$paginator->currentPage() - 1 : null,
          'limit' => \$paginator->perPage(),
          'pages' => \$paginator->lastPage(),
        ],
        'message' => useMsg::get('{$lcModule}.retrieved'),
      ];
    } catch (\\Exception \$e) {
      return [
        'success' => false,
        'statusCode' => 500,
        'message' => useMsg::get('{$lcModule}.retrieval_error'),
      ];
    }
  }
}
EOD;
file_put_contents(
  $basePath . "/UseCases/FindAll{$moduleName}.php",
  $findAllUseCaseContent,
);

// UseCase - FindByUid
$findByUidUseCaseContent = <<<EOD
<?php

namespace App\\Modules\\{$moduleName}\\UseCases;

use App\\Modules\\{$moduleName}\\Repositories\\{$moduleName}RepositoryInterface;
use App\\Modules\\{$moduleName}\\msg\\useMsg;

class FindByUid{$moduleName}
{
  public function __construct(
    private {$moduleName}RepositoryInterface \$repository,
  ) {}

  public function execute(string \$uid): array
  {
    try {
      \$entity = \$this->repository->findByUid(\$uid);

      if (!\$entity) {
        return [
          'success' => false,
          'statusCode' => 404,
          'message' => useMsg::get('{$lcModule}.not_found'),
        ];
      }

      return [
        'success' => true,
        'data' => \$entity,
        'message' => useMsg::get('{$lcModule}.retrieved'),
      ];
    } catch (\\Exception \$e) {
      return [
        'success' => false,
        'statusCode' => 500,
        'message' => useMsg::get('{$lcModule}.retrieval_error'),
      ];
    }
  }
}
EOD;
file_put_contents(
  $basePath . "/UseCases/FindByUid{$moduleName}.php",
  $findByUidUseCaseContent,
);

// UseCase - Update
$updateUseCaseContent = <<<EOD
<?php

namespace App\\Modules\\{$moduleName}\\UseCases;

use App\\Modules\\{$moduleName}\\Repositories\\{$moduleName}RepositoryInterface;
use App\\Modules\\{$moduleName}\\msg\\useMsg;

class Update{$moduleName}
{
  public function __construct(
    private {$moduleName}RepositoryInterface \$repository,
  ) {}

  public function execute(string \$uid, array \$data): array
  {
    try {
      \$entity = \$this->repository->findByUid(\$uid);

      if (!\$entity) {
        return [
          'success' => false,
          'statusCode' => 404,
          'message' => useMsg::get('{$lcModule}.not_found'),
        ];
      }

      \$updated = \$this->repository->update(\$entity, \$data);

      return [
        'success' => true,
        'data' => \$updated,
        'message' => useMsg::get('{$lcModule}.updated'),
      ];
    } catch (\\Exception \$e) {
      return [
        'success' => false,
        'statusCode' => 500,
        'message' => useMsg::get('{$lcModule}.update_error'),
      ];
    }
  }
}
EOD;
file_put_contents(
  $basePath . "/UseCases/Update{$moduleName}.php",
  $updateUseCaseContent,
);

// UseCase - Delete
$deleteUseCaseContent = <<<EOD
<?php

namespace App\\Modules\\{$moduleName}\\UseCases;

use App\\Modules\\{$moduleName}\\Repositories\\{$moduleName}RepositoryInterface;
use App\\Modules\\{$moduleName}\\msg\\useMsg;

class Delete{$moduleName}
{
  public function __construct(
    private {$moduleName}RepositoryInterface \$repository,
  ) {}

  public function execute(string \$uid): array
  {
    try {
      \$entity = \$this->repository->findByUid(\$uid);

      if (!\$entity) {
        return [
          'success' => false,
          'statusCode' => 404,
          'message' => useMsg::get('{$lcModule}.not_found'),
        ];
      }

      \$this->repository->delete(\$entity);

      return [
        'success' => true,
        'message' => useMsg::get('{$lcModule}.deleted'),
      ];
    } catch (\\Exception \$e) {
      return [
        'success' => false,
        'statusCode' => 500,
        'message' => useMsg::get('{$lcModule}.deletion_error'),
      ];
    }
  }
}
EOD;
file_put_contents(
  $basePath . "/UseCases/Delete{$moduleName}.php",
  $deleteUseCaseContent,
);

// Registrar ServiceProvider en config/app.php
$configFile = __DIR__ . '/config/app.php';
$configContent = file_get_contents($configFile);
$success = false;

if ($configContent !== false) {
  $pattern = '/^(\s*)App\\\\Modules\\\\Session\\\\Providers\\\\SessionServiceProvider::class,/m';

  if (preg_match($pattern, $configContent, $matches)) {
    $indentation = $matches[1];
    $anchor = $matches[0];

    $newProviderLine = "{$indentation}App\\\\Modules\\\\{$moduleName}\\\\Providers\\\\{$moduleName}ServiceProvider::class,";
    $replacement = $anchor . "\n" . $newProviderLine;

    $newConfigContent = preg_replace($pattern, $replacement, $configContent, 1);

    if ($newConfigContent !== null && $newConfigContent !== $configContent) {
      if (file_put_contents($configFile, $newConfigContent)) {
        $success = true;
      }
    }
  }
}

echo "Módulo {$moduleName} creado con éxito en app/Modules/{$moduleName}\n";
echo "\n";

if ($success) {
  echo "El ServiceProvider ha sido registrado automáticamente en 'config/app.php'.\n";
} else {
  echo "ADVERTENCIA: No se pudo registrar el ServiceProvider automáticamente.\n";
  echo "Por favor, añade la siguiente línea en el array 'providers' de 'config/app.php':\n";
  echo "   App\\Modules\\{$moduleName}\\Providers\\{$moduleName}ServiceProvider::class,\n";
}

// Agregar permisos CRUD básicos en Permission.php
$permissionFile = __DIR__ . '/app/Modules/Rol/Enums/Permission.php';
if (file_exists($permissionFile)) {
  $permissionContent = file_get_contents($permissionFile);
  if ($permissionContent !== false) {
    $newPermissions = "\n\t// {$moduleName}\n";
    $newPermissions .= "\tcase {$moduleName} = '{$moduleName}';\n";
    $newPermissions .= "\tcase {$moduleName}_READ = '{$moduleName}.read';\n";
    $newPermissions .= "\tcase {$moduleName}_CREATE = '{$moduleName}.create';\n";
    $newPermissions .= "\tcase {$moduleName}_UPDATE = '{$moduleName}.update';\n";
    $newPermissions .= "\tcase {$moduleName}_DELETE = '{$moduleName}.delete';\n";

    $updatedContent = str_replace(
      "\n}",
      $newPermissions . "\n}",
      $permissionContent,
    );

    if (file_put_contents($permissionFile, $updatedContent)) {
      echo "\nPermisos CRUD para {$moduleName} añadidos exitosamente a Permission.php\n";
    } else {
      echo "\nADVERTENCIA: No se pudieron añadir los permisos para {$moduleName} en Permission.php\n";
    }
  }
}

echo "\n";
echo "Pasos siguientes recomendados:\n";
echo "1. Define los campos de tu modelo en app/Modules/{$moduleName}/Models/{$moduleName}.php\n";
echo "2. Revisa y personaliza la migración en app/Modules/{$moduleName}/Migrations/\n";
echo "3. Define las reglas de validación en los Request files\n";
echo "4. Implementa la lógica de negocio en los UseCases según sea necesario\n";
