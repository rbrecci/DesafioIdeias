# Justificativa da Demanda Escolhida

**Desafio de Ideias | SAGA SENAI de Inovação**

| | |
|---|---|
| Product Owner | Rafael Brecci de Souza |
| Grupo | Enzo Avanze, Felipe Bertaco, Vitor Canali, João Pedro, Nicolas Fernandes e Julia Possebon |
| Turma | 2IEDS |
| Data | 28/06/2026 |
| Demanda | nº 12569, Gestão de Laboratórios e Ativos |
| Empresa | W K Soluções Industriais |
| Escola | (SP) 6.02 Escola SENAI Engº Octávio Marcondes Ferraz |

---

O grupo escolheu a demanda Gestão de Laboratórios e Ativos (nº 12569), da empresa W K Soluções
Industriais. A empresa controla seus equipamentos e ordens de serviço por anotações físicas e
registros manuais, um processo lento e sujeito a erro, perda de dados e falta de padronização.
Não se sabe com rapidez onde está um equipamento, qual seu histórico de manutenção ou qual o
setor responsável, e os colaboradores relatam divergências entre documentos.

A escolha não partiu de preferência do grupo. Analisamos as 34 demandas de Tecnologia da
Informação abertas em São Paulo, avaliando viabilidade de entrega no semestre e valor técnico.
Esta se destacou por ter escopo delimitado, requisitos já detalhados pela empresa e não exigir
hardware específico nem infraestrutura cara: temos condições de entregar funcionando, e não apenas
de prototipar.

A ausência de controle centralizado gera retrabalho, compra duplicada e equipamentos parados, além
de inviabilizar a prestação de contas. A própria empresa apontou como benefícios esperados a
redução de inconsistências, a agilidade no acesso à informação e a redução de custos operacionais.

Desenvolvimento de Sistemas se aplica diretamente: modelagem de dados para ativos, setores e
movimentações; banco de dados para centralizar o que hoje está disperso; desenvolvimento web para
acesso simultâneo; controle de acesso por perfil; e engenharia de requisitos, para converter um
processo informal em regras explícitas.

Como ideia inicial, propomos um sistema web com etiqueta QR em cada equipamento: apontar a câmera
do celular abre a ficha do ativo, e movimentá-lo entre setores passa a ser escanear a etiqueta e
indicar o destino. O histórico ficará em registro permanente, no qual nada é editado ou apagado,
apenas acrescentado, e é isso que garante a rastreabilidade solicitada. O sistema contemplará
também as ordens de serviço.
