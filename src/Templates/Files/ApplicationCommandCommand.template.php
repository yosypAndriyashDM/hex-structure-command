%%php_open_tag%%

namespace %%namespace%%;

%%use_definitions%%

class %%class_name%% implements ApplicationServiceRequest
{
    public function __construct(
%%application_command_command_construct_class_parameters%%
    )
    {
    }

%%application_command_command_class_parameters_getters%%
}
