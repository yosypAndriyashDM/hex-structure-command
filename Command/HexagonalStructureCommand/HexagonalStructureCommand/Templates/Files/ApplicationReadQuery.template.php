%%php_open_tag%%

namespace %%namespace%%;

%%use_definitions%%

class %%class_name%% implements ApplicationServiceRequest
{

    public function __construct(
%%application_read_query_construct_args%%
    )
    {
    }

%%application_read_query_getters_body%%
}
