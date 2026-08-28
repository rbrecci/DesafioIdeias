-- AtivoLab — dados iniciais. Rode depois do schema.sql.
-- Usuario admin: admin@ativolab.local / senha: ativolab123
-- TROQUE ESSA SENHA no primeiro acesso.

INSERT INTO setores (nome, sigla) VALUES
  ('Laboratorio de Informatica', 'LAB-INFO'),
  ('Laboratorio de Eletronica',  'LAB-ELET'),
  ('Manutencao',                 'MANUT'),
  ('Almoxarifado',               'ALMOX'),
  ('Administrativo',             'ADM');

INSERT INTO categorias (nome, prefixo) VALUES
  ('Notebook',            'NB'),
  ('Desktop',             'DT'),
  ('Monitor',             'MON'),
  ('Projetor',            'PROJ'),
  ('Instrumento de medicao','INST'),
  ('Ferramenta',          'FERR'),
  ('Rede',                'NET');

INSERT INTO usuarios (nome, matricula, email, senha_hash, papel, setor_id) VALUES
  ('Administrador', 'ADM001', 'admin@ativolab.local',
   '$2y$12$KDXL18oKgB2bDITnJBAeh.9tBVNBNsjUwq.4iBqLFyR8geqmZBlOG', 'admin', 5);

INSERT INTO ativos (patrimonio, nome, categoria_id, setor_id, local_atual, numero_serie, fabricante, modelo, data_aquisicao, valor, status, criado_por) VALUES
  ('NB-000001',   'Notebook Dell Latitude 3420', 1, 1, 'Bancada 01', 'SN7734A1', 'Dell',     'Latitude 3420', '2023-03-14', 4200.00, 'em_uso',        1),
  ('NB-000002',   'Notebook Lenovo ThinkPad E14', 1, 1, 'Bancada 02', 'SN7734A2', 'Lenovo',  'ThinkPad E14',  '2023-03-14', 4850.00, 'disponivel',    1),
  ('DT-000001',   'Desktop montado i5 16GB',      2, 1, 'Bancada 03', 'SN5510B7', 'Montado', 'i5-12400',      '2022-08-02', 3100.00, 'em_uso',        1),
  ('MON-000001',  'Monitor LG 24 polegadas',      3, 1, 'Bancada 03', 'SN2210C4', 'LG',      '24MK430H',      '2022-08-02',  720.00, 'em_uso',        1),
  ('PROJ-000001', 'Projetor Epson PowerLite',     4, 5, 'Sala de reuniao', 'SN9001D2','Epson','PowerLite X49','2021-11-20', 2600.00, 'emprestado',    1),
  ('INST-000001', 'Multimetro Fluke 117',         5, 2, 'Armario A', 'SN4417E9', 'Fluke',    '117',           '2020-05-11', 1950.00, 'disponivel',    1),
  ('INST-000002', 'Osciloscopio Rigol DS1054Z',   5, 2, 'Bancada 05', 'SN4418E1', 'Rigol',   'DS1054Z',       '2021-02-08', 3400.00, 'em_manutencao', 1),
  ('FERR-000001', 'Estacao de solda Hakko FX-888D',6, 2,'Bancada 06', 'SN8888F3', 'Hakko',   'FX-888D',       '2022-01-19',  980.00, 'disponivel',    1),
  ('NET-000001',  'Switch Cisco 24 portas',       7, 3, 'Rack 01',    'SN1024G8', 'Cisco',   'SG250-24',      '2021-07-30', 2750.00, 'em_uso',        1),
  ('NB-000003',   'Notebook Acer Aspire 5',       1, 4, 'Prateleira B','SN7734A3','Acer',    'Aspire 5',      '2024-02-05', 3600.00, 'disponivel',    1);

INSERT INTO movimentacoes (ativo_id, setor_origem_id, setor_destino_id, local_origem, local_destino, responsavel_id, observacao) VALUES
  (7, 2, 3, 'Bancada 05', 'Oficina', 1, 'Enviado para calibracao'),
  (5, 5, 5, 'Almoxarifado', 'Sala de reuniao', 1, 'Emprestimo para treinamento');
