%%php_open_tag%%

namespace %%namespace%%;

%%use_definitions%%

class %%class_name%%
{
    public function __construct(
        private array $collection
    )
    {
    }

    public static function create(array $collection): self
    {
        return new static(
            collection: $collection
        );
    }

    public function toArray($output = []): array
    {
        foreach($this->collection as $item) {
            $output[] = $item;
        }

        return $output;
    }
}