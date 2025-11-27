# M-Pesa Integration - Complete Deployment Checklist

## Pre-Deployment Phase

### Phase 1: Code Review & Testing
- [ ] All PHP files have no syntax errors
- [ ] JavaScript file tested in multiple browsers
- [ ] Credentials verified and correct
- [ ] HTTPS certificate is valid and not expired
- [ ] All file permissions are correct (755 for PHP, 644 for JS)

### Phase 2: Security Hardening
- [ ] Change admin dashboard password (mpesa-dashboard.php)
- [ ] Remove or password-protect mpesa-test-simulate.php
- [ ] Configure firewall to allow only HTTPS (443)
- [ ] Disable directory listing (.htaccess with Options -Indexes)
- [ ] Implement rate limiting on API endpoints
- [ ] Add CORS headers if frontend/backend on different domains

### Phase 3: File Uploads & Directory Setup
- [ ] Upload donate.html to /public_html/
- [ ] Upload mpesa-payment.js to /public_html/
- [ ] Upload mpesa-stk-push.php to /public_html/
- [ ] Upload mpesa-callback.php to /public_html/
- [ ] Upload mpesa-config.php to /public_html/
- [ ] Upload mpesa-dashboard.php to /public_html/
- [ ] Create mpesa_logs directory
- [ ] Set permissions: chmod 755 mpesa_logs/
- [ ] Copy .env.example to .env (on server)
- [ ] Update .env with production credentials
- [ ] Add .env to .gitignore

---

## M-Pesa Configuration Phase

### Phase 4: M-Pesa Portal Setup
- [ ] Log in to Safaricom Developer Portal
- [ ] Verify Business Short Code: 174379
- [ ] Verify Consumer Key matches configuration
- [ ] Verify Consumer Secret matches configuration
- [ ] Update Callback URL: https://unifreelancers.work/mpesa-callback.php
- [ ] Update Timeout URL (if available)
- [ ] Enable STK Push API
- [ ] Whitelist server IP (if required)
- [ ] Test API connectivity from M-Pesa portal

### Phase 5: SSL/HTTPS Verification
- [ ] Domain has valid SSL certificate
- [ ] Certificate is not self-signed
- [ ] Certificate not expired
- [ ] All resources load over HTTPS
- [ ] Mixed content warnings resolved
- [ ] Redirect HTTP to HTTPS implemented

---

## Testing Phase

### Phase 6: Functional Testing
- [ ] Donation form displays correctly
- [ ] Form validation works (frontend)
- [ ] All required fields are mandatory
- [ ] Phone number format validation works
- [ ] Amount validation works (min/max)
- [ ] Tab switching works (Message/M-Pesa)
- [ ] Error messages display correctly

### Phase 7: Backend Testing
- [ ] STK Push request can reach M-Pesa API
- [ ] Access token generation successful
- [ ] Payment prompt reaches phone
- [ ] Callback URL receives M-Pesa responses
- [ ] Transactions logged correctly
- [ ] Success/failure handling works

### Phase 8: End-to-End Testing
- [ ] Complete test payment with real M-Pesa
- [ ] Confirm SMS received from M-Pesa
- [ ] Transaction logged in mpesa_logs/
- [ ] Dashboard shows transaction
- [ ] Phone number properly masked in logs
- [ ] Amount correctly recorded

### Phase 9: Error Handling Testing
- [ ] Invalid credentials → appropriate error
- [ ] Network timeout → graceful error
- [ ] Malformed request → validation error
- [ ] Duplicate transaction → handled correctly
- [ ] Missing callback → logged and monitored
- [ ] Database/file write errors → caught and logged

---

## Deployment Phase

### Phase 10: Go Live
- [ ] Set environment to 'production' (not sandbox)
- [ ] Disable debug mode
- [ ] Remove test simulator file OR password protect
- [ ] Update documentation with production URLs
- [ ] Notify team of deployment
- [ ] Monitor logs closely for first 24 hours
- [ ] Test mobile responsiveness on actual devices
- [ ] Test on slow network/3G

### Phase 11: Monitoring Setup
- [ ] Set up log monitoring
- [ ] Configure alerts for failed transactions
- [ ] Create daily backup of mpesa_logs
- [ ] Monitor server disk space
- [ ] Set up uptime monitoring for donation page
- [ ] Create alert for API response errors

### Phase 12: Documentation & Training
- [ ] Update internal documentation
- [ ] Train team on dashboard access
- [ ] Document emergency procedures
- [ ] Create support contact list
- [ ] Document refund procedures
- [ ] Create troubleshooting guide for team

---

## Post-Deployment Phase

### Phase 13: First Week Monitoring
- [ ] Daily log review
- [ ] Monitor transaction success rate
- [ ] Check for error patterns
- [ ] Verify callback processing
- [ ] Test dashboard access
- [ ] Monitor page load times
- [ ] Check error logs for any issues

### Phase 14: First Month Tasks
- [ ] Analyze donation patterns
- [ ] Calculate transaction fees
- [ ] Review user feedback
- [ ] Optimize form based on usage
- [ ] Verify all logs properly archived
- [ ] Plan for scaling if needed

---

## Maintenance Checklist (Ongoing)

### Weekly
- [ ] Review new transactions in logs
- [ ] Check for error patterns
- [ ] Verify callback processing working
- [ ] Monitor disk space usage
- [ ] Check SSL certificate expiration date

### Monthly
- [ ] Archive old logs
- [ ] Generate donation report
- [ ] Review and update documentation
- [ ] Test emergency procedures
- [ ] Backup transaction data
- [ ] Review security settings

### Quarterly
- [ ] Security audit
- [ ] Performance review
- [ ] Database cleanup (if using DB)
- [ ] Update dependencies
- [ ] Test disaster recovery

### Annually
- [ ] SSL certificate renewal
- [ ] Full system audit
- [ ] Penetration testing (if possible)
- [ ] Update M-Pesa API documentation review
- [ ] Plan for next year improvements

---

## Files Deployed

| File | Size | Type | Status |
|------|------|------|--------|
| donate.html | ~8KB | Frontend | ✓ Deployed |
| mpesa-payment.js | ~4KB | Frontend | ✓ Deployed |
| mpesa-stk-push.php | ~5KB | Backend | ✓ Deployed |
| mpesa-callback.php | ~4KB | Backend | ✓ Deployed |
| mpesa-config.php | ~2KB | Config | ✓ Deployed |
| mpesa-dashboard.php | ~8KB | Tool | ✓ Deployed |
| mpesa-test-simulate.php | ~3KB | Tool | ⚠️ Remove/Protect |
| .env | N/A | Config | ✓ Created locally |

---

## Support & Contact

### Emergency Contact
- **M-Pesa Support:** +254 7811 000 111
- **Server Admin:** [Your contact info]
- **Backup Admin:** [Backup contact info]

### Documentation Links
- M-Pesa Developer Portal: https://developer.safaricom.co.ke/
- API Documentation: https://safaricom.entropix.io/
- Your Dashboard: https://unifreelancers.work/mpesa-dashboard.php

---

## Rollback Procedure (If Issues)

1. **Immediate Actions:**
   - [ ] Disable M-Pesa payment form (hide tab)
   - [ ] Keep message donation form active
   - [ ] Notify team of issue

2. **Investigation:**
   - [ ] Check mpesa_logs/ for errors
   - [ ] Review recent changes
   - [ ] Check M-Pesa portal status

3. **Remediation:**
   - [ ] Apply fix to code
   - [ ] Test in staging (if available)
   - [ ] Redeploy fixed version
   - [ ] Verify in mpesa_logs/
   - [ ] Re-enable M-Pesa payment

4. **Post-Incident:**
   - [ ] Document what went wrong
   - [ ] Update procedures
   - [ ] Communicate with team
   - [ ] Plan to prevent recurrence

---

## Performance Benchmarks

Expected performance metrics:

- **Form Load Time:** < 2 seconds
- **STK Push Initiation:** < 5 seconds
- **Payment Processing:** Immediate (real-time)
- **Callback Receipt:** < 30 seconds
- **Dashboard Load:** < 3 seconds
- **Success Rate Goal:** > 95%

---

## Success Criteria

✅ Integration is considered successful when:

1. At least 10 successful test transactions completed
2. Dashboard shows all transactions correctly
3. Logs properly formatted and accessible
4. No errors in server logs for 24 hours
5. SSL certificate verified
6. Mobile responsiveness confirmed
7. All team trained on procedures
8. Backup/disaster recovery tested

---

## Sign-Off

- [ ] Developer: _______________________ Date: _______
- [ ] QA/Tester: _______________________ Date: _______
- [ ] Project Manager: _______________________ Date: _______
- [ ] Operations: _______________________ Date: _______

---

**Deployment Date:** _______________
**Go-Live Time:** _______________
**Deployment Status:** Ready ✓

**Notes:**
_________________________________________________________________
_________________________________________________________________
_________________________________________________________________

---

**Last Updated:** November 28, 2025
**Version:** 1.0
