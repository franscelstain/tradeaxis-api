# One Document, One Authoritative Role Standard

Every semantic document MUST have exactly one authoritative role. Cross-role references and short supporting summaries are allowed, but a document MUST NOT simultaneously own strategy + implementation, evidence + decision, finding + evidence, or current authority + historical verdict.

Role is registered in `DOCUMENT_ROLE_REGISTRY.csv`. Historical mixed wording preserved inside a frozen source does not gain a second current authority: only the registered role has current semantic effect.
