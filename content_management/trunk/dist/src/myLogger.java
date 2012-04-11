package maarchcm;

import java.io.IOException;
import java.util.logging.FileHandler;
import java.util.logging.Level;
import java.util.logging.Logger;
import java.util.logging.SimpleFormatter;

public class myLogger {
    
    private String loggerFile;
    private FileHandler fh;
    private Logger logger;

    myLogger(String pathTologs) {
        this.loggerFile = pathTologs + "maarchCM.log";
        this.logger = Logger.getLogger("maarchCM");
        try {
            // This block configure the logger with handler and formatter
            this.fh = new FileHandler(this.loggerFile, true);
            this.logger.addHandler(this.fh);
            this.logger.setLevel(Level.ALL);
            SimpleFormatter formatter = new SimpleFormatter();
            this.fh.setFormatter(formatter);
            // the following statement is used to log any messages   
            this.logger.log(Level.INFO,"init the logger");
        } catch (SecurityException e) {
            System.out.println(e);
        } catch (IOException e) {
            System.out.println(e);
        }
    }
    
    public void log(String message, Level level) {
        this.logger.log(level, message);
    }
}