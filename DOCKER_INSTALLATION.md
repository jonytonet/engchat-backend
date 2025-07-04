# 🐳 GUIA DE INSTALAÇÃO DO DOCKER - Windows

## 📋 **Pré-requisitos do Sistema**

### **Windows 10/11 Requirements:**
- ✅ **Windows 10** versão 1903+ (Build 18362+) ou **Windows 11**
- ✅ **WSL 2** habilitado (recomendado)
- ✅ **Hyper-V** habilitado ou **WSL 2**
- ✅ **Virtualização** habilitada no BIOS/UEFI
- ✅ Pelo menos **4GB RAM** (8GB+ recomendado)
- ✅ **20GB** de espaço livre em disco

---

## 🚀 **OPÇÃO 1: Docker Desktop (RECOMENDADO)**

### **1. Download:**
- 🌐 **Site oficial:** https://www.docker.com/products/docker-desktop/
- 📥 **Download direto:** https://desktop.docker.com/win/stable/Docker%20Desktop%20Installer.exe

### **2. Instalação:**
```powershell
# Execute o instalador como Administrador
# ✅ Marque: "Use WSL 2 instead of Hyper-V"
# ✅ Marque: "Add shortcut to desktop"
```

### **3. Primeira Configuração:**
1. **Reiniciar** o computador após instalação
2. **Abrir Docker Desktop**
3. **Aceitar** os termos de uso
4. **Configurar** recursos (opcional):
   - Memory: 4-8GB
   - CPUs: 2-4 cores
   - Disk: 20GB+

### **4. Verificar Instalação:**
```powershell
# Abrir PowerShell ou CMD
docker --version
docker-compose --version
docker run hello-world
```

---

## ⚡ **OPÇÃO 2: Docker via WSL 2 (Avançado)**

### **1. Habilitar WSL 2:**
```powershell
# Execute como Administrador
dism.exe /online /enable-feature /featurename:Microsoft-Windows-Subsystem-Linux /all /norestart
dism.exe /online /enable-feature /featurename:VirtualMachinePlatform /all /norestart
```

### **2. Instalar Kernel WSL 2:**
- 📥 **Download:** https://wslstorestorage.blob.core.windows.net/wslblob/wsl_update_x64.msi
- ▶️ **Executar** o instalador

### **3. Configurar WSL 2:**
```powershell
wsl --set-default-version 2
wsl --install -d Ubuntu
```

### **4. Instalar Docker Engine no WSL:**
```bash
# Dentro do Ubuntu WSL
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo usermod -aG docker $USER
```

---

## 🔧 **TROUBLESHOOTING COMUM**

### **❌ Virtualização não habilitada:**
```
1. Reiniciar e entrar no BIOS/UEFI
2. Procurar por "Virtualization Technology" ou "Intel VT-x" ou "AMD-V"
3. Habilitar a opção
4. Salvar e reiniciar
```

### **❌ WSL 2 não funciona:**
```powershell
# Verificar versão Windows
winver

# Atualizar Windows se necessário
# Ir em: Configurações > Atualização e Segurança > Windows Update
```

### **❌ Docker Desktop não inicia:**
```powershell
# Reset factory
# Docker Desktop > Settings > Reset > Reset to factory defaults

# Ou reinstalar completamente
```

### **❌ "Docker daemon not running":**
```bash
# Iniciar Docker Desktop manualmente
# Ou via PowerShell:
net start com.docker.service
```

---

## ✅ **VERIFICAÇÃO FINAL**

### **Comandos para testar:**
```powershell
# Versões
docker --version
docker-compose --version

# Container de teste
docker run hello-world

# Verificar se pode baixar imagens
docker pull nginx

# Listar containers
docker ps

# Verificar sistema
docker system info
```

### **Resultado esperado:**
```bash
docker --version
# Docker version 24.0.x, build xxxxx

docker-compose --version  
# Docker Compose version v2.21.x
```

---

## 🎯 **PRÓXIMOS PASSOS APÓS INSTALAÇÃO**

### **1. Voltar ao EngChat:**
```bash
cd c:\Users\jony.tonet\Desktop\Dev\engchat-backend
```

### **2. Testar nosso ambiente:**
```bash
# Nosso helper script
.\sail.bat

# Ou comando direto
vendor\bin\sail.bat up -d
```

### **3. Verificar serviços:**
- 🌐 **App:** http://localhost:8000
- 📚 **Swagger:** http://localhost:8000/api/documentation  
- 📧 **Mailpit:** http://localhost:8025
- 🐰 **RabbitMQ:** http://localhost:15672

---

## 💡 **DICAS IMPORTANTES**

### **Performance:**
- ✅ Use **WSL 2** em vez de Hyper-V
- ✅ Aloque **4-8GB RAM** para Docker
- ✅ Use **SSD** se possível

### **Segurança:**
- ✅ Docker Desktop é **seguro** para desenvolvimento
- ✅ Containers são **isolados**
- ✅ **Não expor** portas desnecessárias

### **Backup:**
```bash
# Export containers (se necessário)
docker export container_name > backup.tar

# Export volumes
docker run --rm -v volume_name:/data -v $(pwd):/backup ubuntu tar czf /backup/backup.tar.gz -C /data .
```

---

## 📞 **SUPORTE**

### **Se tiver problemas:**
1. 📖 **Documentação oficial:** https://docs.docker.com/desktop/windows/
2. 🎯 **StackOverflow:** https://stackoverflow.com/questions/tagged/docker
3. 💬 **Discord Docker:** https://discord.gg/docker
4. 📧 **Issues específicos:** Compartilhe prints/logs

### **Informações úteis para debug:**
```powershell
# Info do sistema
systeminfo
docker system info
docker version
wsl --list --verbose
```

---

**🔥 Após instalar o Docker, execute `.\sail.bat up` e vamos continuar o desenvolvimento!**

**⏱️ Tempo estimado de instalação:** 15-30 minutos (dependendo da internet)
