@echo off
REM ==============================================================================
REM Build Script for Question 3: Student Admission and Profile Management Portal
REM Checks for Maven; if not found, compiles and packages WAR using local JDK.
REM ==============================================================================

echo [Student Portal Q3] Starting build process...

where mvn >nul 2>nul
if %ERRORLEVEL% EQU 0 (
    echo [Student Portal Q3] Found Maven. Building with Maven package...
    mvn clean package
    goto done
)

echo [Student Portal Q3] Maven command not found in PATH.
echo [Student Portal Q3] Using JDK javac and jar to compile and package student-portal.war...

if not exist "target\classes" mkdir "target\classes"
if not exist "target\war-staging\WEB-INF\classes" mkdir "target\war-staging\WEB-INF\classes"
if not exist "target\war-staging\WEB-INF\lib" mkdir "target\war-staging\WEB-INF\lib"

set CP=lib\jakarta.servlet-api-6.0.0.jar;lib\jakarta.servlet.jsp-api-3.1.1.jar;lib\mysql-connector-j-8.3.0.jar

javac -cp "%CP%" -d target\classes src\main\java\com\studentportal\*.java
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Compilation failed!
    exit /b %ERRORLEVEL%
)

copy /y src\main\resources\* target\classes\ >nul
xcopy /s /e /y src\main\webapp\* target\war-staging\ >nul
xcopy /s /e /y target\classes\* target\war-staging\WEB-INF\classes\ >nul
copy /y lib\mysql-connector-j-8.3.0.jar target\war-staging\WEB-INF\lib\ >nul

jar -cvf target\student-portal.war -C target\war-staging . >nul
rmdir /s /q target\war-staging

:done
echo [Student Portal Q3] Build completed successfully!
echo [Student Portal Q3] Output artifact: target\student-portal.war
