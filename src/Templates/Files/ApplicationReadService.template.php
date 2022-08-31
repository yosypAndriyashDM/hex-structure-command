%%php_open_tag%%

namespace %%namespace%%;

%%use_definitions%%
class %%class_name%%
{
%%application_read_service_domain_service_definition%%

%%application_read_service_class_constructor%%
    public function execute(
%%application_read_service_query_class_type%% $query
    ): ApplicationServiceResponse
    {
%%application_read_service_execute_prepare_params_from_query%%
%%application_read_service_domain_call_stub%%

        return new %%application_read_service_response_class_type%%(
%%application_read_service_response_domain_args%%
        );
    }
}
