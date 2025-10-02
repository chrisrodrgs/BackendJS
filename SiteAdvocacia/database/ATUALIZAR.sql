-- Script completo para corrigir a estrutura do banco
USE felix_advocacia;

-- 1. Adicionar coluna OAB se não existir
ALTER TABLE advogados ADD COLUMN IF NOT EXISTS oab VARCHAR(20) AFTER especialidade;

-- 2. Atualizar OAB dos advogados existentes
UPDATE advogados SET oab = 'SP123456A' WHERE email = 'admin@felixadvocacia.com.br';
UPDATE advogados SET oab = 'SP234567B' WHERE email = 'ana.silva@felixadvocacia.com.br';
UPDATE advogados SET oab = 'SP345678C' WHERE email = 'carlos.oliveira@felixadvocacia.com.br';
UPDATE advogados SET oab = 'SP456789D' WHERE email = 'mariana.santos@felixadvocacia.com.br';

-- 3. Verificar se a coluna foi adicionada corretamente
DESCRIBE advogados;

-- 4. Ver os dados atualizados
SELECT id, nome, email, oab, especialidade FROM advogados;